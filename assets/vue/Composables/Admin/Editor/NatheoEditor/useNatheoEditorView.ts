/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vue de Natheo-Editor passée au rendu du thème : commandes des modules résolues, traduites et rangées par groupe,
 * fenêtres des modules, raccourcis des commandes à fenêtre, infobulles des liens vers une page ou un mot-clé
 */
import { computed, markRaw, shallowRef, type Component } from 'vue';
import { Extension, type Editor } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';
import type { Node as ProseMirrorNode } from '@tiptap/pm/model';
import { useNatheoEditor } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditor';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type {
  NatheoEditorCommand,
  NatheoEditorContext,
  NatheoEditorGroup,
  NatheoEditorModule,
  NatheoEditorOpenDialog,
  NatheoEditorTranslate,
  NatheoEditorView,
} from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Mot-clé d'e-mail utilisé comme adresse d'un lien */
const KEYWORD_HREF = /^\[\[[\w.-]+\]\]$/;

/**
 * Système d'Apple : raccourcis écrits avec des symboles
 */
export function isMac(): boolean {
  return typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.userAgent);
}

/**
 * Raccourci lisible : Mod-Shift-z donne ⌘⇧Z sur Mac, Ctrl+Maj+Z ailleurs
 * @param shortcut raccourci au format TipTap
 * @param mac
 * @param shift nom de la touche Maj sur un clavier autre que Mac
 */
export function formatShortcut(shortcut: string, mac: boolean, shift: string): string {
  const parts = shortcut.split('-');
  const key = parts.pop() ?? '';
  const names: Record<string, string> = mac
    ? { Mod: '⌘', Alt: '⌥', Shift: '⇧', Ctrl: '⌃' }
    : { Mod: 'Ctrl', Alt: 'Alt', Shift: shift, Ctrl: 'Ctrl' };
  const modifiers = parts.map((part) => names[part] ?? part);
  const label = key.length === 1 ? key.toUpperCase() : key;
  return mac ? modifiers.join('') + label : [...modifiers, label].join('+');
}

/**
 * Commandes des modules, dans l'ordre des modules
 * @param modules
 * @param context
 */
export function commandsOf(modules: NatheoEditorModule[], context: NatheoEditorContext): NatheoEditorCommand[] {
  return modules.flatMap((module) =>
    typeof module.commands === 'function' ? module.commands(context) : (module.commands ?? [])
  );
}

/**
 * Infobulles des liens vers une page du site (P#id) ou vers l'adresse d'un mot-clé
 * @param doc
 * @param t
 */
function linkTitles(doc: ProseMirrorNode, t: NatheoEditorTranslate): DecorationSet {
  const decorations: Decoration[] = [];
  doc.descendants((node, position) => {
    const href = String(node.marks.find((mark) => mark.type.name === 'link')?.attrs.href ?? '');
    const page = /^P#(\d+)$/.exec(href);
    if (!node.isText || (page === null && !KEYWORD_HREF.test(href))) return;
    const attributes =
      page !== null
        ? { title: t('admin_ui.natheo_editor.link_page', { id: page[1] }), class: 'natheo-editor-internal' }
        : { title: t('admin_ui.natheo_editor.link_keyword', { keyword: href }) };
    decorations.push(Decoration.inline(position, position + node.nodeSize, attributes));
  });
  return DecorationSet.create(doc, decorations);
}

/**
 * Crée l'éditeur et sa vue
 * @param props props du contrat, réactives
 * @param emit émet le Markdown modifié
 * @param modules modules retenus pour le préréglage
 * @param t traducteur
 */
export function useNatheoEditorView(
  props: AdminEditorProps,
  emit: (value: string) => void,
  modules: NatheoEditorModule[],
  t: NatheoEditorTranslate
): NatheoEditorView {
  const context: NatheoEditorContext = { props };
  const commands = computed(() => commandsOf(modules, context));
  const owners = new Map(modules.flatMap((module) => commandsOf([module], context).map((c) => [c.id, module])));
  const dialog = shallowRef<NatheoEditorOpenDialog | null>(null);
  const mac = isMac();

  const openDialog = (command: NatheoEditorCommand): void => {
    owners
      .get(command.id)
      ?.dialog?.()
      .then((component: Component) => (dialog.value = { component: markRaw(component), command: command.id }))
      .catch((error: unknown) => console.error(error));
  };

  // Fenêtres ouvertes au clavier (Mod-k) et infobulles des liens, propres à la vue
  const viewModule = defineNatheoEditorModule({
    id: 'natheoEditorView',
    always: true,
    extensions: () => [
      Extension.create({
        name: 'natheoEditorView',
        addKeyboardShortcuts() {
          return Object.fromEntries(
            commandsOf(modules, context)
              .filter((command) => command.run === undefined && command.shortcut !== undefined)
              .map((command) => [
                command.shortcut as string,
                () => {
                  if (!state.editable.value || state.source.value) return false;
                  openDialog(command);
                  return true;
                },
              ])
          );
        },
        addProseMirrorPlugins() {
          return [
            new Plugin({
              key: new PluginKey('natheoEditorLinkTitles'),
              state: {
                init: (_, editorState) => linkTitles(editorState.doc, t),
                apply: (transaction, titles) => (transaction.docChanged ? linkTitles(transaction.doc, t) : titles),
              },
              props: {
                decorations(editorState) {
                  return this.getState(editorState);
                },
              },
            }),
          ];
        },
      }),
    ],
  });

  const state = useNatheoEditor(props, emit, [...modules, viewModule]);
  const editor = (): Editor => state.editor.value;

  const isActive = (command: NatheoEditorCommand): boolean => command.isActive?.(editor()) ?? false;
  const group = (name: NatheoEditorGroup): NatheoEditorCommand[] =>
    commands.value.filter((command) => command.group === name && (command.visible?.(editor()) ?? true));
  const label = (command: NatheoEditorCommand): string => (command.translated ? command.label : t(command.label));
  const shortcut = (command: NatheoEditorCommand): string | undefined =>
    command.shortcut && formatShortcut(command.shortcut, mac, t('admin_ui.natheo_editor.key_shift'));

  return {
    ...state,
    modules,
    props,
    commands,
    group,
    // Conteneurs (listes, citation) avant le paragraphe qu'ils contiennent
    currentBlock: () => [...group('list'), ...group('block'), ...group('heading').reverse()].find(isActive) ?? null,
    run: (command) => {
      if (!state.editable.value || state.source.value) return;
      if (command.run) command.run(editor());
      else openDialog(command);
    },
    isActive,
    isEnabled: (command) => state.editable.value && !state.source.value && (command.isEnabled?.(editor()) ?? true),
    label,
    hint: (command) => (command.hint && !command.translated ? t(command.hint) : command.hint),
    shortLabel: (command) => (command.shortLabel ? t(command.shortLabel) : label(command)),
    shortcut,
    tooltip: (command) => {
      const keys = shortcut(command);
      return keys ? `${label(command)} (${keys})` : label(command);
    },
    t,
    dialog,
    closeDialog: () => {
      dialog.value = null;
      if (!state.source.value) editor().commands.focus();
    },
  };
}

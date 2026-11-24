import { afterEach, describe, expect, it } from 'vitest';
import { defineComponent, nextTick } from 'vue';
import { flushPromises } from '@vue/test-utils';
import { modulesForPreset, natheoEditorModules } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { commandsOf, formatShortcut } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditorView';
import { editorProps } from '@tests/vue/Composables/Admin/Editor/NatheoEditor/support';
import EditorSurface from '@admin/Editors/NatheoEditor/Parts/EditorSurface.vue';
import { mountView } from '@tests/vue/Components/Admin/Editors/NatheoEditor/support';
import type { NatheoEditorCommand } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const byId = (commands: NatheoEditorCommand[], id: string): NatheoEditorCommand => {
  const found = commands.find((command) => command.id === id);
  if (!found) throw new Error(id);
  return found;
};

describe('formatShortcut', () => {
  it('écrit les touches en symboles sur Mac', () => {
    expect(formatShortcut('Mod-Shift-z', true, 'Maj')).toBe('⌘⇧Z');
    expect(formatShortcut('Mod-Alt-2', true, 'Maj')).toBe('⌘⌥2');
  });

  it('écrit les touches en toutes lettres ailleurs', () => {
    expect(formatShortcut('Mod-Shift-z', false, 'Maj')).toBe('Ctrl+Maj+Z');
    expect(formatShortcut('Mod-k', false, 'Maj')).toBe('Ctrl+K');
  });
});

describe('commandsOf', () => {
  it('lit les commandes fixes et celles construites depuis les props', () => {
    const props = editorProps({ keywords: [{ keyword: '[[user.login]]', label: 'Identifiant' }] });
    const commands = commandsOf(natheoEditorModules(), { props });
    expect(commands.map((command) => command.id)).toContain('bold');
    expect(commands.map((command) => command.id)).toContain('keyword:user.login');
  });
});

describe('useNatheoEditorView', () => {
  let unmount: () => void = () => undefined;
  afterEach(() => unmount());

  it("traduit les libellés et ajoute le raccourci à l'infobulle", async () => {
    const { wrapper, view } = await mountView(null);
    unmount = () => wrapper.unmount();
    const bold = byId(view.commands.value, 'bold');
    expect(view.label(bold)).toBe('command.bold');
    expect(view.tooltip(bold)).toMatch(/^command\.bold \((⌘B|Ctrl\+B)\)$/);
  });

  it('garde tels quels les libellés déjà traduits des mots-clés', async () => {
    const { wrapper, view } = await mountView(null, {
      keywords: [{ keyword: '[[user.login]]', label: 'Identifiant' }],
    });
    unmount = () => wrapper.unmount();
    const [keyword] = view.group('keyword');
    expect(view.label(keyword)).toBe('Identifiant');
    expect(view.hint(keyword)).toBe('[[user.login]]');
  });

  it('montre les commandes du tableau seulement dans un tableau', async () => {
    const { wrapper, view, editor } = await mountView(null, {
      modelValue: 'Texte\n\n| A | B |\n| --- | --- |\n| 1 | 2 |',
    });
    unmount = () => wrapper.unmount();
    expect(view.group('table')).toEqual([]);
    editor().commands.setTextSelection(editor().state.doc.content.size - 3);
    expect(view.group('table').map((command) => command.id)).toContain('tableAddRow');
  });

  it('donne le bloc courant, la liste avant son paragraphe', async () => {
    const { wrapper, view, editor } = await mountView(null, { modelValue: '### Titre\n\n- point' });
    unmount = () => wrapper.unmount();
    editor().commands.setTextSelection(2);
    expect(view.currentBlock()?.id).toBe('heading3');
    editor().commands.setTextSelection(editor().state.doc.content.size - 2);
    expect(view.currentBlock()?.id).toBe('bulletList');
  });

  it('désactive les commandes en mode source et en lecture seule', async () => {
    const { wrapper, view, props } = await mountView(null, { modelValue: 'Texte' });
    unmount = () => wrapper.unmount();
    const bold = byId(view.commands.value, 'bold');
    expect(view.isEnabled(bold)).toBe(true);
    view.toggleSource();
    expect(view.isEnabled(bold)).toBe(false);
    view.toggleSource();
    props.readonly = true;
    await nextTick();
    expect(view.isEnabled(bold)).toBe(false);
  });

  it('ouvre la fenêtre du module pour une commande sans action, aussi par son raccourci', async () => {
    const Dialog = defineComponent({ render: () => null });
    const modules = [
      ...modulesForPreset(natheoEditorModules(), 'basic').filter((module) => module.id !== 'link'),
      defineNatheoEditorModule({
        id: 'note',
        extensions: () => [],
        dialog: () => Promise.resolve(Dialog),
        commands: [{ id: 'note', label: 'note', icon: 'info', group: 'insert', shortcut: 'Mod-j' }],
      }),
    ];
    const { wrapper, view, editor } = await mountView(null, { modelValue: 'Texte' }, modules);
    unmount = () => wrapper.unmount();
    view.run(byId(view.commands.value, 'note'));
    await flushPromises();
    expect(view.dialog.value?.command).toBe('note');
    view.closeDialog();
    expect(view.dialog.value).toBeNull();

    editor().commands.keyboardShortcut('Mod-j');
    await flushPromises();
    expect(view.dialog.value?.command).toBe('note');
  });

  it('donne une infobulle aux liens vers une page du site et vers un mot-clé', async () => {
    const { wrapper } = await mountView(EditorSurface, { modelValue: '[Accueil](P#12) et [profil]([[user.url]])' });
    unmount = () => wrapper.unmount();
    const titles = wrapper.findAll('[title]').map((element) => element.attributes('title'));
    expect(titles).toEqual(['link_page(12)', 'link_keyword([[user.url]])']);
    expect(wrapper.find('.natheo-editor-internal').text()).toBe('Accueil');
  });
});

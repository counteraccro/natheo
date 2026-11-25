<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Natheo-Editor dans Natheo-Atelier : rien au repos, barre compacte pendant la saisie (menus « Paragraphe » et
 * « Insérer », gras, italique, effacer la mise en forme, lien), barre flottante sur une sélection et menu « / » sur une ligne vide
 */
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { PluginKey, TextSelection } from '@tiptap/pm/state';
import { BubbleMenu } from '@tiptap/vue-3/menus';
import { Suggestion, exitSuggestion, type SuggestionProps } from '@tiptap/suggestion';
import type { Editor } from '@tiptap/core';
import { UiIcon } from '@ui';
import EditorButton from '@admin/Editors/NatheoEditor/Parts/EditorButton.vue';
import EditorMenu from '@admin/Editors/NatheoEditor/Parts/EditorMenu.vue';
import EditorTableBar from '@admin/Editors/NatheoEditor/Parts/EditorTableBar.vue';
import EditorSurface from '@admin/Editors/NatheoEditor/Parts/EditorSurface.vue';
import EditorFooter from '@admin/Editors/NatheoEditor/Parts/EditorFooter.vue';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';
import { editorFrameClasses } from '@/vue/Composables/Admin/Editor/NatheoEditor/editorClasses';
import { useRovingToolbar } from '@/vue/Composables/Admin/Editor/NatheoEditor/useRovingToolbar';
import type {
  NatheoEditorCommand,
  NatheoEditorGroup,
  NatheoEditorRenderProps,
} from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<NatheoEditorRenderProps>();
const view = props.view;
const { onFocusin, onKeydown } = useRovingToolbar();

/** Commandes en bouton dans la barre compacte, les autres passent par les menus */
const QUICK = ['bold', 'italic', 'link', 'clearFormatting'];

/** Groupes du menu « / », avec leur intitulé */
const SLASH_GROUPS: { groups: NatheoEditorGroup[]; label: string }[] = [
  { groups: ['heading'], label: t('admin_ui.natheo_editor.slash.group_heading') },
  { groups: ['list', 'block'], label: t('admin_ui.natheo_editor.slash.group_block') },
  { groups: ['insert'], label: t('admin_ui.natheo_editor.slash.group_insert') },
  { groups: ['keyword'], label: t('admin_ui.natheo_editor.slash.group_keyword') },
];

const blocks = computed(() => [...view.group('heading'), ...view.group('list'), ...view.group('block')]);
const inserts = computed(() =>
  [...view.group('insert'), ...view.group('keyword')].filter((command) => !QUICK.includes(command.id))
);
const quick = computed(() => view.commands.value.filter((command) => QUICK.includes(command.id)));
const bubble = computed(() => [...view.group('inline'), ...view.group('insert').filter((c) => c.id === 'link')]);
const current = computed(() => view.currentBlock() ?? view.group('heading')[0] ?? null);

// Barre montrée tant que le champ, l'un de ses menus ou sa fenêtre a le focus
const root = ref<HTMLElement | null>(null);
const focused = ref(false);
const menus = ref(0);
const active = computed(() => focused.value || menus.value > 0 || view.dialog.value !== null);

const onFocusout = (event: FocusEvent): void => {
  if (!root.value?.contains(event.relatedTarget as Node | null)) focused.value = false;
};

/**
 * Barre flottante sur une sélection de texte dans l'éditeur visuel
 * @param editor
 */
const showBubble = ({ editor }: { editor: Editor }): boolean => {
  // Mise à jour différée de la barre, qui peut arriver après la destruction de l'éditeur
  if (editor.isDestroyed) return false;
  const selection = editor.state.selection;
  return (
    view.editable.value &&
    !view.source.value &&
    editor.view.hasFocus() &&
    selection instanceof TextSelection &&
    !selection.empty &&
    !editor.isActive('codeBlock')
  );
};

/**
 * Texte comparé sans casse ni accents
 * @param text
 */
const normalize = (text: string): string => text.normalize('NFD').replace(/\p{M}/gu, '').toLowerCase();

/** Menu « / » : commandes filtrées par le texte tapé après « / » */
const slash = reactive({
  open: false,
  query: '',
  index: 0,
  top: 0,
  left: 0,
  items: [] as NatheoEditorCommand[],
  choose: null as ((command: NatheoEditorCommand) => void) | null,
});
const slashKey = new PluginKey('natheoEditorSlash');

const slashSections = computed(() =>
  SLASH_GROUPS.map((section) => ({
    label: view.t(section.label),
    items: slash.items.filter((command) => section.groups.includes(command.group)),
  })).filter((section) => section.items.length)
);
// Ordre de parcours au clavier : celui des sections
const slashOrder = computed(() => slashSections.value.flatMap((section) => section.items));

const filterSlash = (query: string): NatheoEditorCommand[] => {
  const typed = normalize(query);
  return SLASH_GROUPS.flatMap((section) => section.groups.flatMap((group) => view.group(group))).filter(
    (command) =>
      view.isEnabled(command) &&
      [view.label(command), view.hint(command) ?? '', command.markdown ?? ''].some((text) =>
        normalize(text).includes(typed)
      )
  );
};

const placeSlash = (suggestion: SuggestionProps<NatheoEditorCommand>): void => {
  const rect = suggestion.clientRect?.();
  slash.items = suggestion.items;
  slash.query = suggestion.query;
  slash.choose = (command) => suggestion.command(command);
  slash.index = Math.min(slash.index, Math.max(0, suggestion.items.length - 1));
  if (rect) {
    slash.top = rect.bottom + 6;
    slash.left = rect.left;
  }
};

const chooseSlash = (command: NatheoEditorCommand | undefined): void => {
  if (command) slash.choose?.(command);
};

onMounted(() => {
  view.editor.value.registerPlugin(
    Suggestion<NatheoEditorCommand, NatheoEditorCommand>({
      pluginKey: slashKey,
      editor: view.editor.value,
      char: '/',
      startOfLine: true,
      // Ligne vide d'un paragraphe : « / » et la recherche y sont seuls
      allow: ({ state, range }) => {
        const parent = state.doc.resolve(range.from).parent;
        return (
          view.editable.value &&
          parent.type.name === 'paragraph' &&
          parent.textContent === state.doc.textBetween(range.from, range.to)
        );
      },
      items: ({ query }) => filterSlash(query),
      command: ({ editor, range, props: command }) => {
        editor.chain().focus().deleteRange(range).run();
        view.run(command);
      },
      render: () => ({
        onStart: (suggestion) => {
          slash.index = 0;
          placeSlash(suggestion);
          slash.open = true;
        },
        onUpdate: placeSlash,
        onExit: () => {
          slash.open = false;
          slash.choose = null;
        },
        onKeyDown: ({ event, view: editorView }) => {
          const count = slashOrder.value.length;
          if (event.key === 'Escape') {
            exitSuggestion(editorView, slashKey);
            return true;
          }
          if (count === 0) return false;
          if (event.key === 'ArrowDown') slash.index = (slash.index + 1) % count;
          else if (event.key === 'ArrowUp') slash.index = (slash.index - 1 + count) % count;
          else if (event.key === 'Enter') chooseSlash(slashOrder.value[slash.index]);
          else return false;
          return true;
        },
      }),
    }),
    // En tête : ses touches (Entrée, flèches) passent avant celles des autres extensions
    (plugin, plugins) => [plugin, ...plugins]
  );
});
onBeforeUnmount(() => {
  if (!view.editor.value.isDestroyed) view.editor.value.unregisterPlugin(slashKey);
});

const slashId = (index: number): string => `${view.props.id}-slash-${index}`;

// Choix courant du menu « / » annoncé depuis la zone de saisie, qui garde le focus
watch(
  () => [slash.open, slash.index, slashOrder.value.length] as const,
  ([open, index, count]) => {
    const dom = view.editor.value.view.dom;
    if (open && count > 0) dom.setAttribute('aria-activedescendant', slashId(index));
    else dom.removeAttribute('aria-activedescendant');
  }
);
</script>

<template>
  <div
    ref="root"
    :class="editorFrameClasses(view, 'border-line-strong')"
    class="natheo-editor-atelier relative rounded-[10px] border"
    @focusin="focused = true"
    @focusout="onFocusout"
  >
    <div
      v-if="view.editable.value && active"
      class="sticky top-0 z-[2] rounded-t-[10px] bg-bg motion-safe:animate-[ui-pop_.16s_var(--ease)]"
    >
      <div
        ref="toolbar"
        role="toolbar"
        :aria-label="view.t('admin_ui.natheo_editor.toolbar')"
        :aria-controls="view.props.id"
        class="flex flex-wrap items-center gap-0.5 border-b border-line px-1.5 py-[5px]"
        @focusin="onFocusin"
        @keydown="onKeydown"
      >
        <EditorMenu
          v-if="current"
          :view="view"
          :commands="blocks"
          :label="view.label(current)"
          :aria-label="view.t('admin_ui.natheo_editor.block_menu', { block: view.label(current) })"
          :icon="current.icon"
          named
          @open="menus++"
          @close="menus--"
        />
        <EditorMenu
          v-if="inserts.length"
          :view="view"
          :commands="inserts"
          :label="view.t('admin_ui.natheo_editor.insert_menu')"
          icon="plus"
          named
          @open="menus++"
          @close="menus--"
        />
        <span v-if="quick.length" class="mx-1.5 h-5 w-px bg-line-strong" aria-hidden="true" />
        <EditorButton v-for="command in quick" :key="command.id" :view="view" :command="command" />
        <span class="ml-auto pr-1.5 text-xs text-fg-subtle max-sm:hidden">
          {{ view.t('admin_ui.natheo_editor.slash_hint') }}
        </span>
      </div>
      <EditorTableBar :view="view" />
    </div>
    <EditorSurface :view="view" class="[&_.natheo-editor-content]:px-[22px] [&_.natheo-editor-content]:py-[18px]" />
    <EditorFooter :view="view" />

    <BubbleMenu
      v-if="view.editable.value"
      :editor="view.editor.value"
      :should-show="showBubble"
      :plugin-key="'natheoEditorBubble'"
    >
      <div
        role="toolbar"
        :aria-label="view.t('admin_ui.natheo_editor.bubble_toolbar')"
        class="flex items-center gap-0.5 rounded-[10px] bg-raised p-1 shadow-overlay motion-safe:animate-[ui-pop_.14s_var(--ease)]"
      >
        <EditorButton v-for="command in bubble" :key="command.id" :view="view" :command="command" small />
      </div>
    </BubbleMenu>

    <Teleport to="body">
      <div
        v-if="slash.open"
        :style="{ top: `${slash.top}px`, left: `${slash.left}px` }"
        class="fixed z-40 w-[300px] overflow-hidden rounded-lg bg-raised text-fg shadow-overlay motion-safe:animate-[ui-pop_.14s_var(--ease)]"
        @mousedown.prevent
      >
        <div class="flex items-center gap-2 border-b border-line px-3 py-2.5 text-sm text-fg-subtle">
          <UiIcon name="search" class="size-4" />
          <span v-if="slash.query" class="font-semibold text-fg">{{ slash.query }}</span>
          <span v-else>{{ view.t('admin_ui.natheo_editor.slash.search') }}</span>
        </div>
        <ul
          v-if="slashOrder.length"
          role="listbox"
          :aria-label="view.t('admin_ui.natheo_editor.slash.list')"
          class="m-0 max-h-[300px] list-none overflow-auto p-1.5"
        >
          <template v-for="section in slashSections" :key="section.label">
            <li role="presentation" class="px-2 pt-2 pb-1 text-xs font-semibold text-fg-subtle">
              {{ section.label }}
            </li>
            <li
              v-for="command in section.items"
              :id="slashId(slashOrder.indexOf(command))"
              :key="command.id"
              role="option"
              :aria-selected="slashOrder.indexOf(command) === slash.index"
              class="group grid cursor-pointer grid-cols-[32px_1fr_auto] items-center gap-2.5 rounded-md px-2 py-1.5 aria-selected:bg-primary-soft"
              @mouseenter="slash.index = slashOrder.indexOf(command)"
              @click="chooseSlash(command)"
            >
              <span
                class="grid size-8 place-items-center rounded-[7px] border border-line bg-bg text-fg-muted group-aria-selected:border-transparent group-aria-selected:text-on-primary-soft"
              >
                <UiIcon :name="command.icon" class="size-4" />
              </span>
              <span>
                <b class="block font-semibold">{{ view.label(command) }}</b>
                <small v-if="view.hint(command)" class="text-xs text-fg-subtle">{{ view.hint(command) }}</small>
              </span>
              <kbd v-if="command.markdown" class="font-sans text-xs text-fg-subtle">{{ command.markdown }}</kbd>
            </li>
          </template>
        </ul>
        <p v-else class="m-0 p-3.5 text-sm text-fg-subtle">{{ view.t('admin_ui.natheo_editor.slash.empty') }}</p>
        <p class="m-0 border-t border-line px-3 py-2 text-xs text-fg-subtle">
          {{ view.t('admin_ui.natheo_editor.slash.help') }}
        </p>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Natheo-Editor dans Natheo-Studio : barre d'outils fixe en haut du champ, commandes groupées et séparées, menu des
 * titres et des mots-clés, sous-barre « Tableau », pied avec le mode Markdown et le nombre de mots
 */
import { computed } from 'vue';
import EditorButton from '@admin/Editors/NatheoEditor/Parts/EditorButton.vue';
import EditorMenu from '@admin/Editors/NatheoEditor/Parts/EditorMenu.vue';
import EditorTableBar from '@admin/Editors/NatheoEditor/Parts/EditorTableBar.vue';
import EditorSurface from '@admin/Editors/NatheoEditor/Parts/EditorSurface.vue';
import EditorFooter from '@admin/Editors/NatheoEditor/Parts/EditorFooter.vue';
import { editorFrameClasses } from '@/vue/Composables/Admin/Editor/NatheoEditor/editorClasses';
import { useRovingToolbar } from '@/vue/Composables/Admin/Editor/NatheoEditor/useRovingToolbar';
import type {
  NatheoEditorCommand,
  NatheoEditorGroup,
  NatheoEditorRenderProps,
} from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<NatheoEditorRenderProps>();
const { onFocusin, onKeydown } = useRovingToolbar();

/** Groupes de boutons, dans l'ordre de la barre */
const BUTTON_GROUPS: NatheoEditorGroup[] = ['inline', 'list', 'block', 'insert', 'other'];

const headings = computed(() => props.view.group('heading'));
const keywords = computed(() => props.view.group('keyword'));
const buttonGroups = computed(() =>
  BUTTON_GROUPS.map((group) => props.view.group(group)).filter((commands) => commands.length)
);
const history = computed(() => props.view.group('history'));

/** Titre courant, le premier choix (paragraphe) sinon */
const heading = computed(
  (): NatheoEditorCommand | undefined => headings.value.find(props.view.isActive) ?? headings.value[0]
);
</script>

<template>
  <div :class="editorFrameClasses(view, 'border-field')" class="relative rounded-[10px] border">
    <div v-if="view.editable.value" class="sticky top-0 z-[2] rounded-t-[10px] bg-bg">
      <div
        ref="toolbar"
        role="toolbar"
        :aria-label="view.t('admin_ui.natheo_editor.toolbar')"
        :aria-controls="view.props.id"
        class="flex flex-wrap items-center gap-0.5 border-b border-line p-1.5"
        @focusin="onFocusin"
        @keydown="onKeydown"
      >
        <EditorButton v-for="command in history" :key="command.id" :view="view" :command="command" />
        <template v-if="heading">
          <span v-if="history.length" class="mx-1.5 h-5 w-px bg-line-strong" aria-hidden="true" />
          <EditorMenu
            :view="view"
            :commands="headings"
            :label="view.label(heading)"
            :aria-label="view.t('admin_ui.natheo_editor.block_menu', { block: view.label(heading) })"
            checkable
          />
        </template>
        <template v-for="(commands, index) in buttonGroups" :key="index">
          <span
            v-if="index > 0 || history.length || heading"
            class="mx-1.5 h-5 w-px bg-line-strong"
            aria-hidden="true"
          />
          <EditorButton v-for="command in commands" :key="command.id" :view="view" :command="command" />
        </template>
        <template v-if="keywords.length">
          <span class="mx-1.5 h-5 w-px bg-line-strong" aria-hidden="true" />
          <EditorMenu
            :view="view"
            :commands="keywords"
            :label="view.t('admin_ui.natheo_editor.keyword_menu')"
            icon="tag"
            named
          />
        </template>
      </div>
      <EditorTableBar :view="view" />
    </div>
    <EditorSurface :view="view" />
    <EditorFooter :view="view" />
  </div>
</template>

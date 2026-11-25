<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pied de Natheo-Editor : bascules du rendu du site et du mode Markdown, nombre de mots
 */
import { UiIcon } from '@ui';
import { FOOTER_TOGGLE_CLASSES } from '@/vue/Composables/Admin/Editor/NatheoEditor/editorClasses';
import type { NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

defineProps<{ view: NatheoEditorView }>();
</script>

<template>
  <div class="flex min-h-[38px] items-center gap-1 border-t border-line px-2 py-1">
    <button
      v-if="view.siteTheme && !view.source.value"
      type="button"
      :aria-pressed="view.siteRender.value"
      :class="FOOTER_TOGGLE_CLASSES"
      @click="view.toggleSiteRender()"
    >
      <UiIcon name="palette" class="size-4" />
      {{ view.t('admin_ui.natheo_editor.site_render') }}
    </button>
    <button
      v-if="view.editable.value"
      type="button"
      :aria-pressed="view.source.value"
      :class="FOOTER_TOGGLE_CLASSES"
      @click="view.toggleSource()"
    >
      <UiIcon name="code" class="size-4" />
      {{ view.t('admin_ui.natheo_editor.markdown') }}
    </button>
    <span class="ml-auto pr-1.5 text-[12.5px] text-fg-subtle tabular-nums">
      {{ view.t('admin_ui.natheo_editor.words', { count: view.words.value }) }}
    </span>
  </div>
</template>

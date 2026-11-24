<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Zone de saisie de Natheo-Editor : éditeur visuel, ou texte Markdown en mode source ; hauteur entre le minimum du
 * champ et 60 % de l'écran ; nom du thème du site quand son rendu est montré
 */
import { computed } from 'vue';
import { EditorContent } from '@tiptap/vue-3';
import type { NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<{ view: NatheoEditorView }>();

const style = computed(() => ({
  '--natheo-editor-min': props.view.height.value.minHeight,
  '--natheo-editor-max': props.view.height.value.maxHeight,
  '--natheo-editor-raw-label': JSON.stringify(props.view.t('admin_ui.natheo_editor.raw_html')),
}));

const onInput = (event: Event): void => props.view.setSourceText((event.target as HTMLTextAreaElement).value);
</script>

<template>
  <div class="natheo-editor-surface relative" :style="style">
    <EditorContent v-show="!view.source.value" :editor="view.editor.value" />
    <span
      v-if="view.siteRender.value && view.siteTheme && !view.source.value"
      class="pointer-events-none absolute top-2 right-3 rounded-full bg-surface px-2 py-px text-[11px] font-semibold text-fg-muted"
    >
      {{ view.siteTheme.label }}
    </span>
    <textarea
      v-if="view.source.value"
      class="natheo-editor-source"
      :value="view.sourceText.value"
      :aria-labelledby="view.props.labelledBy"
      :aria-label="view.props.labelledBy ? undefined : view.props.label"
      :aria-describedby="view.props.describedBy"
      :aria-invalid="view.props.invalid || undefined"
      :readonly="!view.editable.value"
      :lang="view.props.lang"
      spellcheck="false"
      @input="onInput"
    />
  </div>
</template>

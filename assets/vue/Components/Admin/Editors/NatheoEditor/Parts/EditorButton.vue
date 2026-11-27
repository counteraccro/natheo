<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bouton d'une commande de Natheo-Editor : icône, infobulle avec le raccourci, état pressé ; reste atteignable au
 * clavier quand il est désactivé (barre d'outils)
 */
import { UiIcon, UiTooltip } from '@ui';
import type { NatheoEditorCommand, NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = withDefaults(defineProps<{ view: NatheoEditorView; command: NatheoEditorCommand; small?: boolean }>(), {
  small: false,
});

const run = (): void => {
  if (props.view.isEnabled(props.command)) props.view.run(props.command);
};
</script>

<template>
  <UiTooltip :text="view.tooltip(command)" placement="top-center">
    <button
      type="button"
      :aria-label="view.label(command)"
      :aria-pressed="command.isActive ? view.isActive(command) : undefined"
      :aria-disabled="view.isEnabled(command) ? undefined : true"
      :class="small ? 'size-[30px]' : 'size-8'"
      class="inline-grid shrink-0 cursor-pointer place-items-center rounded-[7px] border-0 bg-transparent text-fg-muted hover:bg-surface hover:text-fg aria-pressed:bg-primary-soft aria-pressed:text-on-primary-soft aria-disabled:cursor-default aria-disabled:opacity-40 aria-disabled:hover:bg-transparent"
      @mousedown.prevent
      @click="run"
    >
      <UiIcon :name="command.icon" class="size-[18px]" />
    </button>
  </UiTooltip>
</template>

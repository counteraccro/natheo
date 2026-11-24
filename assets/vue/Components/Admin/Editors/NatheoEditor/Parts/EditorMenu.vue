<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu de commandes de Natheo-Editor (titres, mots-clés, insertion) : bouton nommé, choix courant coché ou icônes
 */
import { nextTick } from 'vue';
import { UiDropdown, UiDropdownItem, UiIcon, type IconName } from '@ui';
import type { NatheoEditorCommand, NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = withDefaults(
  defineProps<{
    view: NatheoEditorView;
    commands: NatheoEditorCommand[];
    /** Texte du bouton */
    label: string;
    /** Nom accessible du bouton quand son texte est le choix courant */
    ariaLabel?: string;
    icon?: IconName;
    /** Choix unique : la commande active est cochée, sinon chaque commande montre son icône */
    checkable?: boolean;
    /** Bouton sans bordure (barre compacte) */
    named?: boolean;
  }>(),
  { checkable: false, named: false }
);
const emit = defineEmits<{ open: []; close: [] }>();

const disabled = (): boolean => !props.view.editable.value || props.view.source.value;

/**
 * Lance la commande une fois le menu fermé, pour que le focus revienne dans la zone de saisie
 * @param command
 */
const select = (command: NatheoEditorCommand): void => {
  nextTick(() => props.view.run(command));
};

/**
 * Syntaxe ou forme insérée rappelée à droite (##, [[user.login]])
 * @param command
 */
const detail = (command: NatheoEditorCommand): string | undefined =>
  command.markdown ?? (command.translated ? command.hint : undefined);
</script>

<template>
  <UiDropdown :label="label" placement="bottom-start" @open="emit('open')" @close="emit('close')">
    <template #trigger="{ props: trigger, setRef }">
      <button
        :ref="setRef"
        v-bind="trigger"
        type="button"
        :aria-label="ariaLabel"
        :disabled="disabled()"
        :class="
          named
            ? 'border-transparent hover:bg-surface aria-expanded:bg-surface'
            : 'min-w-33 justify-between border-line-strong hover:border-field'
        "
        class="inline-flex h-[30px] shrink-0 cursor-pointer items-center gap-1.5 rounded-[7px] border bg-transparent pr-2 pl-2.5 text-sm font-semibold text-fg disabled:cursor-default disabled:opacity-40"
        @mousedown.prevent
      >
        <span class="inline-flex items-center gap-1.5">
          <UiIcon v-if="icon" :name="icon" class="size-4 text-fg-muted" />
          {{ label }}
        </span>
        <UiIcon name="chevron-down" class="size-4 text-fg-subtle" />
      </button>
    </template>
    <UiDropdownItem
      v-for="command in commands"
      :key="command.id"
      :type="checkable ? 'radio' : 'item'"
      :checked="checkable ? view.isActive(command) : undefined"
      :icon="checkable ? undefined : command.icon"
      :shortcut="detail(command)"
      :disabled="!view.isEnabled(command)"
      @select="select(command)"
    >
      {{ view.label(command) }}
    </UiDropdownItem>
  </UiDropdown>
</template>

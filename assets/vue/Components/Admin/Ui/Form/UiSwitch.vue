<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Interrupteur (role="switch") avec son libellé et une description facultative ; dans un UiField, reçoit son
 * identifiant, son aide et ses erreurs
 */
import { computed, useId } from 'vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';

const props = defineProps<{ label: string; description?: string; disabled?: boolean }>();
const model = defineModel<boolean>({ default: false });
const uid = useId();
const control = useFieldControl();
const describedBy = computed(
  () =>
    [props.description && `ui-switch-${uid}-desc`, control.value['aria-describedby']].filter(Boolean).join(' ') ||
    undefined
);

// Événement change comme un champ natif, pour que le UiField parent efface ses erreurs
const toggle = (event: MouseEvent): void => {
  model.value = !model.value;
  event.currentTarget?.dispatchEvent(new Event('change', { bubbles: true }));
};
</script>

<template>
  <div class="flex items-start justify-between gap-4">
    <div class="flex flex-col">
      <span :id="`ui-switch-${uid}`" class="font-semibold text-fg">{{ label }}</span>
      <span v-if="description" :id="`ui-switch-${uid}-desc`" class="text-sm text-fg-subtle">{{ description }}</span>
    </div>
    <button
      :id="control.id"
      type="button"
      role="switch"
      :aria-checked="model"
      :aria-labelledby="`ui-switch-${uid}`"
      :aria-describedby="describedBy"
      :aria-invalid="control['aria-invalid']"
      :disabled="disabled"
      class="ui-ring relative mt-0.5 h-6 w-[42px] shrink-0 cursor-pointer rounded-full transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-50"
      :class="model ? 'bg-primary' : 'bg-field'"
      @click="toggle"
    >
      <span
        class="absolute top-[3px] size-[18px] rounded-full bg-white shadow-[0_1px_2px_rgb(0_0_0/.25)] transition-[left] duration-200 ease-out-soft"
        :class="model ? 'left-[21px]' : 'left-[3px]'"
      />
    </button>
  </div>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Case à cocher native, état intermédiaire facultatif ; sans libellé visible, un aria-label est attendu
 */
import { ref, watchPostEffect } from 'vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';

defineOptions({ inheritAttrs: false });

const props = defineProps<{ label?: string; description?: string; indeterminate?: boolean; disabled?: boolean }>();
const model = defineModel<boolean>({ default: false });
const input = ref<HTMLInputElement | null>(null);
const control = useFieldControl();

watchPostEffect(() => {
  if (input.value) {
    input.value.indeterminate = !!props.indeterminate;
  }
});
</script>

<template>
  <label v-if="label" class="inline-flex cursor-pointer items-start gap-2.5 text-fg" :class="disabled && 'cursor-not-allowed opacity-60'">
    <input
      ref="input"
      v-model="model"
      v-bind="{ ...control, ...$attrs }"
      type="checkbox"
      :disabled="disabled"
      class="mt-0.5 size-4 shrink-0 cursor-pointer accent-primary disabled:cursor-not-allowed"
    />
    <span class="flex flex-col">
      <span>{{ label }}</span>
      <span v-if="description" class="text-sm text-fg-subtle">{{ description }}</span>
    </span>
  </label>
  <input
    v-else
    ref="input"
    v-model="model"
    v-bind="{ ...control, ...$attrs }"
    type="checkbox"
    :disabled="disabled"
    class="size-4 shrink-0 cursor-pointer align-middle accent-primary disabled:cursor-not-allowed"
  />
</template>

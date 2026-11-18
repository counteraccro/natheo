<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste déroulante native stylée
 */
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';
import type { SelectOption } from '@/ts/Admin/Ui/Form/UiSelect.type';

defineOptions({ inheritAttrs: false });

withDefaults(defineProps<{ options?: SelectOption[]; size?: 'sm' | 'md'; placeholder?: string }>(), {
  options: () => [],
  size: 'md',
});

const model = defineModel<string | number | null>();
const control = useFieldControl();
</script>

<template>
  <div class="relative inline-flex w-full">
    <select
      v-model="model"
      v-bind="{ ...control, ...$attrs }"
      :class="[CONTROL_CLASSES, 'cursor-pointer appearance-none pr-8', size === 'sm' ? 'h-8 pl-2.5 text-sm' : 'h-9 pl-3']"
    >
      <option v-if="placeholder" :value="null" disabled>{{ placeholder }}</option>
      <slot>
        <option v-for="option in options" :key="option.value" :value="option.value" :disabled="option.disabled">
          {{ option.label }}
        </option>
      </slot>
    </select>
    <UiIcon
      name="chevron-down"
      class="pointer-events-none absolute top-1/2 right-2 size-4 -translate-y-1/2 text-fg-subtle"
    />
  </div>
</template>

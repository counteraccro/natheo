<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Zone de texte sur plusieurs lignes, avec compteur de caractères facultatif
 */
import { computed } from 'vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';

defineOptions({ inheritAttrs: false });

withDefaults(defineProps<{ rows?: number; maxlength?: number; counter?: boolean }>(), { rows: 4 });

const model = defineModel<string | null>();
const labels = useUiLabels();
const control = useFieldControl();
const length = computed(() => (model.value ?? '').length);
</script>

<template>
  <textarea
    v-model="model"
    v-bind="{ ...control, ...$attrs }"
    :rows="rows"
    :maxlength="maxlength"
    :class="[CONTROL_CLASSES, 'min-h-20 resize-y px-3 py-2 leading-normal']"
  />
  <p v-if="counter && maxlength" class="-mt-0.5 text-right text-xs text-fg-subtle tabular-nums">
    {{ labels.input.counter(length, maxlength) }}
  </p>
</template>

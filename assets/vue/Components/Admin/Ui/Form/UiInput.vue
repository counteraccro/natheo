<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Champ texte, email, nombre, recherche, mot de passe ou URL, avec compteur de caractères facultatif
 */
import { computed } from 'vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    type?: 'text' | 'email' | 'number' | 'search' | 'password' | 'url';
    maxlength?: number;
    counter?: boolean;
  }>(),
  { type: 'text' }
);

const model = defineModel<string | number | null>();
const labels = useUiLabels();
const control = useFieldControl();
const length = computed(() => String(model.value ?? '').length);
</script>

<template>
  <input
    v-model="model"
    v-bind="{ ...control, ...$attrs }"
    :type="props.type"
    :maxlength="maxlength"
    :class="[CONTROL_CLASSES, 'h-9 px-3']"
  />
  <p v-if="counter && maxlength" class="-mt-0.5 text-right text-xs text-fg-subtle tabular-nums">
    {{ labels.input.counter(length, maxlength) }}
  </p>
</template>

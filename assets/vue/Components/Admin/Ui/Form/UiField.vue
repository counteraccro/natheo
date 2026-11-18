<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Champ de formulaire : libellé, aide et erreur reliés au contrôle placé dans l'emplacement par défaut
 */
import { computed, useId } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { provideField } from '@/vue/Composables/Admin/Ui/useField';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';

const props = defineProps<{
  label: string;
  help?: string;
  error?: string;
  required?: boolean;
  id?: string;
  hideLabel?: boolean;
}>();

const labels = useUiLabels();
const uid = useId();
const id = computed(() => props.id ?? `ui-field-${uid}`);
const helpId = computed(() => `${id.value}-help`);
const errorId = computed(() => `${id.value}-error`);
const describedBy = computed(
  () => [props.error && errorId.value, props.help && helpId.value].filter(Boolean).join(' ') || undefined
);

provideField({
  id,
  describedBy,
  invalid: computed(() => !!props.error),
  required: computed(() => props.required),
});
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label :for="id" class="text-[13.5px] font-semibold text-fg" :class="hideLabel && 'sr-only'">
      {{ label }}<template v-if="required"
        ><span class="ml-0.5 text-danger" aria-hidden="true">*</span
        ><span class="sr-only"> ({{ labels.field.required }})</span></template
      >
    </label>
    <slot :id="id" :described-by="describedBy" :invalid="!!error" />
    <p v-if="error" :id="errorId" class="flex items-start gap-1.5 text-sm font-medium text-danger">
      <UiIcon name="exclamation-circle" class="mt-0.5 size-4" />
      {{ error }}
    </p>
    <p v-if="help" :id="helpId" class="text-sm text-fg-subtle">{{ help }}</p>
  </div>
</template>

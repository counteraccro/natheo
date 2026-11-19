<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Champ de formulaire : libellé, aide et erreurs reliés au contrôle placé dans l'emplacement par défaut ; dans un UiForm,
 * name (chemin de propriété de l'API) lit ses erreurs et les efface dès que le champ est modifié
 */
import { computed, onBeforeUnmount, ref, useId, watch } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { provideField } from '@/vue/Composables/Admin/Ui/useField';
import { useFormContext } from '@/vue/Composables/Admin/Ui/formContext';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';

const props = defineProps<{
  label: string;
  help?: string;
  error?: string | string[];
  /** Chemin de propriété dans les erreurs de l'API (label, translations[en].label) */
  name?: string;
  required?: boolean;
  id?: string;
  hideLabel?: boolean;
}>();

const labels = useUiLabels();
const counter = ref<string>();
const form = useFormContext();
const uid = useId();
const id = computed(() => props.id ?? (props.name && form ? form.fieldId(props.name) : `ui-field-${uid}`));
const helpId = computed(() => `${id.value}-help`);
const errorId = computed(() => `${id.value}-error`);
const messages = computed(() => [
  ...(Array.isArray(props.error) ? props.error : props.error ? [props.error] : []),
  ...(props.name && form ? form.errors.messagesFor(props.name) : []),
]);
const describedBy = computed(
  () => [messages.value.length && errorId.value, props.help && helpId.value].filter(Boolean).join(' ') || undefined
);

watch(
  () => [props.name, props.label] as const,
  ([name, label], previous) => {
    if (!form) return;
    if (previous?.[0] && previous[0] !== name) form.unregister(previous[0]);
    if (name) form.register(name, label);
  },
  { immediate: true }
);
onBeforeUnmount(() => props.name && form?.unregister(props.name));

const clearErrors = (): void => {
  if (props.name) form?.errors.clear(props.name);
};

provideField({
  id,
  describedBy,
  invalid: computed(() => messages.value.length > 0),
  required: computed(() => props.required),
  counter,
});
</script>

<template>
  <div class="flex flex-col gap-1.5" @input="clearErrors" @change="clearErrors">
    <label :for="id" class="text-[13.5px] font-semibold text-fg" :class="hideLabel && 'sr-only'">
      {{ label
      }}<template v-if="required"
        ><span class="ml-0.5 text-danger" aria-hidden="true">*</span
        ><span class="sr-only"> ({{ labels.field.required }})</span></template
      >
    </label>
    <slot :id="id" :described-by="describedBy" :invalid="messages.length > 0" />
    <div v-if="messages.length || counter" class="-mt-0.5 flex items-start gap-3">
      <div v-if="messages.length" :id="errorId" class="flex flex-col gap-1">
        <p
          v-for="(message, index) in messages"
          :key="index"
          class="m-0 flex items-start gap-1.5 text-sm font-medium text-danger"
        >
          <UiIcon name="exclamation-circle" class="mt-0.5 size-4" />
          {{ message }}
        </p>
      </div>
      <p v-if="counter" class="m-0 ml-auto shrink-0 text-xs leading-5 text-fg-subtle tabular-nums">{{ counter }}</p>
    </div>
    <p v-if="help" :id="helpId" class="text-sm text-fg-subtle">{{ help }}</p>
  </div>
</template>

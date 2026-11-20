<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire sans validation du navigateur (le serveur est la seule source des règles) : relie ses champs, ses onglets
 * et son récapitulatif aux erreurs renvoyées par l'API
 */
import { nextTick, provide, reactive } from 'vue';
import { FORM } from '@/vue/Composables/Admin/Ui/formContext';
import { fieldIdOf } from '@/vue/Composables/Admin/Ui/useFormErrors';
import type { FormErrors } from '@/ts/Admin/Ui/Form/UiForm.type';

const props = defineProps<{ errors: FormErrors }>();
const emit = defineEmits<{ submit: [event: SubmitEvent] }>();

const fieldLabels = reactive(new Map<string, string>());
const revealers = new Set<(path: string) => boolean>();

const reveal = async (path: string): Promise<void> => {
  let changed = false;
  revealers.forEach((revealer) => (changed = revealer(path) || changed));
  if (changed) await nextTick();
  const target = document.getElementById(fieldIdOf(path));
  target?.focus();
  target?.scrollIntoView({ block: 'center' });
};

provide(FORM, {
  errors: props.errors,
  register: (path, label) => fieldLabels.set(path, label),
  unregister: (path) => fieldLabels.delete(path),
  labelOf: (path) => fieldLabels.get(path),
  fieldId: fieldIdOf,
  reveal,
  addRevealer: (revealer) => {
    revealers.add(revealer);
    return () => revealers.delete(revealer);
  },
});
</script>

<template>
  <form novalidate class="flex flex-col gap-[18px]" @submit.prevent="emit('submit', $event as SubmitEvent)">
    <slot />
  </form>
</template>

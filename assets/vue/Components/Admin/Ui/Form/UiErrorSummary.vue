<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Récapitulatif des erreurs d'un UiForm : reçoit le focus à chaque réponse en erreur, chaque lien ouvre l'onglet du
 * champ et lui donne le focus
 */
import { computed, nextTick, ref, watch } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useFormContext } from '@/vue/Composables/Admin/Ui/formContext';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { FormErrorItem } from '@/ts/Admin/Ui/Form/UiForm.type';

const form = useFormContext();
if (!form) {
  throw new Error('UiErrorSummary doit être placé dans un UiForm.');
}

const labels = useUiLabels();
const box = ref<HTMLElement | null>(null);

const items = computed<FormErrorItem[]>(() =>
  Object.entries(form.errors.all.value).flatMap(([path, messages]) =>
    messages.map((message) => ({
      path,
      fieldId: path === '' ? null : form.fieldId(path),
      label: path === '' ? '' : (form.labelOf(path) ?? path),
      message,
    }))
  )
);

watch(form.errors.version, async () => {
  if (form.errors.count.value === 0) return;
  await nextTick();
  box.value?.focus();
  box.value?.scrollIntoView({ block: 'nearest' });
});
</script>

<template>
  <div
    v-if="items.length"
    ref="box"
    role="alert"
    tabindex="-1"
    class="flex items-start gap-3 rounded-md border border-[color-mix(in_srgb,var(--danger)_30%,transparent)] bg-danger-soft py-3 pr-3 pl-3.5 text-fg focus-visible:outline-danger"
  >
    <UiIcon name="exclamation-circle" class="mt-px size-5 text-danger" />
    <div class="min-w-0 flex-1">
      <p class="m-0 leading-snug font-[650]">{{ labels.errorSummary.title(form.errors.count.value) }}</p>
      <ul class="m-0 mt-1.5 flex list-none flex-col gap-1 p-0 text-sm">
        <li v-for="(item, index) in items" :key="index">
          <a
            v-if="item.fieldId"
            :href="`#${item.fieldId}`"
            class="rounded-sm font-medium text-danger underline underline-offset-3"
            @click.prevent="form.reveal(item.path)"
            >{{ item.label }} : {{ item.message }}</a
          >
          <span v-else class="text-fg-muted">{{ item.message }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

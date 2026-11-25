<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Question ouverte dans l'arbre d'une FAQ : titre et réponse par langue, rappel d'une question ou d'une catégorie
 * désactivée ; en lecture seule, titre et réponse affichés sans modification
 */
import { computed } from 'vue';
import { UiField, UiInput, UiTextEditor } from '@ui';
import FaqLineFrame from '@admin/Modules/Faq/FaqLineFrame.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { FaqTree, FaqTreeLine } from '@/vue/Composables/Admin/Faq/useFaqTree';
import type { AdminFaqQuestion, AdminFaqTreeCategory } from '@/ts/Admin/Api/AdminFaq.type';

const props = defineProps<{
  tree: FaqTree;
  line: Extract<FaqTreeLine, { type: 'question' }>;
  category: AdminFaqTreeCategory;
  readOnly: boolean;
}>();

const { t } = useAdminContext();
const { data } = props.line.resource;
const isNew = props.line.id === null;

const rowOf = (locale: string) => {
  const rows = (data.value as AdminFaqQuestion).translations;
  let row = rows.find((candidate) => candidate.locale === locale);
  if (!row) {
    row = { locale, title: '', answer: '' };
    rows.push(row);
  }
  return row;
};
const valueOf = (locale: string, field: 'title' | 'answer'): string =>
  (data.value as AdminFaqQuestion | null)?.translations.find((row) => row.locale === locale)?.[field] ?? '';

const heading = computed(() => {
  if (props.readOnly) return t('faq.question.heading_view');
  return isNew ? t('faq.question.heading_new', { category: props.category.title }) : t('faq.question.heading_edit');
});
const offNote = computed(() => {
  const disabled = !isNew && data.value?.disabled === true;
  if (disabled && props.category.disabled) {
    return {
      text: t('faq.question.off_both', { category: props.category.title }),
      action: t('faq.question.enable_both'),
    };
  }
  if (disabled) return { text: t('faq.question.off'), action: t('faq.question.enable') };
  if (props.category.disabled) return { text: t('faq.question.off_category', { category: props.category.title }) };
  return null;
});
</script>

<template>
  <FaqLineFrame
    :tree="tree"
    :line="line"
    :heading="heading"
    id-prefix="faq-question"
    :skeleton-lines="4"
    :off-note="offNote"
    :read-only="readOnly"
  >
    <template #default="{ locale, label, index }">
      <div class="flex flex-col gap-[18px]">
        <UiField
          :name="`translations[${index}].title`"
          :label="t('faq.question.title_label', { language: label })"
          required
        >
          <UiInput
            :model-value="valueOf(locale, 'title')"
            :disabled="readOnly"
            @update:model-value="rowOf(locale).title = String($event ?? '')"
            @keydown.enter.prevent
          />
        </UiField>
        <UiField
          :name="`translations[${index}].answer`"
          :label="t('faq.question.answer_label', { language: label })"
          required
        >
          <UiTextEditor
            :model-value="valueOf(locale, 'answer')"
            preset="full"
            :rows="6"
            :lang="locale"
            :readonly="readOnly"
            @update:model-value="rowOf(locale).answer = String($event ?? '')"
          />
        </UiField>
      </div>
    </template>
  </FaqLineFrame>
</template>

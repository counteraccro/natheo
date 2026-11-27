<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Catégorie ouverte dans l'arbre d'une FAQ : titre par langue (recopie de la langue de l'interface à la création),
 * Entrée pour enregistrer
 */
import { computed } from 'vue';
import { UiCheckbox, UiField, UiInput } from '@ui';
import FaqLineFrame from '@admin/Modules/Faq/FaqLineFrame.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FaqTree, FaqTreeLine } from '@/vue/Composables/Admin/Faq/useFaqTree';
import type { AdminFaqCategory } from '@/ts/Admin/Api/AdminFaq.type';

const props = defineProps<{ tree: FaqTree; line: Extract<FaqTreeLine, { type: 'category' }> }>();

const { t, bootstrap } = useAdminContext();
const locales = bootstrap.locales;
const current = bootstrap.locale;
const currentName = localeName(current, bootstrap.locale);
const { data } = props.line.resource;
const copying = props.line.copying;
const isNew = props.line.id === null;

const titleOf = (locale: string): string =>
  (data.value as AdminFaqCategory | null)?.translations.find((row) => row.locale === locale)?.title ?? '';

const write = (locale: string, value: string): void => {
  const rows = (data.value as AdminFaqCategory).translations;
  const row = rows.find((candidate) => candidate.locale === locale);
  if (row) row.title = value;
  else rows.push({ locale, title: value });
};

const setTitle = (locale: string, value: string): void => {
  write(locale, value);
  if (copying.value && locale === current)
    locales.filter((code) => code !== current).forEach((code) => write(code, value));
};

const setCopying = (value: boolean): void => {
  copying.value = value;
  if (value) setTitle(current, titleOf(current));
};

const isCopied = (locale: string): boolean => isNew && copying.value && locale !== current;
const heading = computed(() => (isNew ? t('faq.category.heading_new') : t('faq.category.heading_edit')));
const offNote = computed(() =>
  !isNew && data.value?.disabled === true ? { text: t('faq.category.off'), action: t('faq.category.enable') } : null
);
</script>

<template>
  <FaqLineFrame
    :tree="tree"
    :line="line"
    :heading="heading"
    id-prefix="faq-category"
    :skeleton-lines="2"
    :off-note="offNote"
  >
    <template #default="{ locale, label, index }">
      <UiField
        :name="`translations[${index}].title`"
        :label="t('faq.category.title_label', { language: label })"
        :help="isCopied(locale) ? t('faq.category.copied', { language: currentName }) : undefined"
        required
      >
        <UiInput
          :model-value="titleOf(locale)"
          :maxlength="255"
          :disabled="isCopied(locale)"
          @update:model-value="setTitle(locale, String($event ?? ''))"
          @keydown.enter.prevent="tree.saveLine()"
        />
        <template v-if="isNew" #below>
          <UiCheckbox
            :model-value="copying"
            :label="t('faq.category.copy', { language: currentName })"
            @update:model-value="setCopying($event)"
          />
        </template>
      </UiField>
    </template>
  </FaqLineFrame>
</template>

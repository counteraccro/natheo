<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Question ouverte dans l'arbre d'une FAQ : titre et réponse par langue, erreurs de l'API, conflit, rappel d'une
 * question ou d'une catégorie désactivée ; en lecture seule, titre et réponse affichés sans modification
 */
import { computed, ref } from 'vue';
import { UiAlert, UiBadge, UiButton, UiField, UiForm, UiInput, UiSkeleton, UiTabs, UiTextEditor } from '@ui';
import FaqLineOffNote from '@admin/Modules/Faq/FaqLineOffNote.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FaqTree, FaqTreeLine } from '@/vue/Composables/Admin/Faq/useFaqTree';
import type { AdminFaqQuestion, AdminFaqTreeCategory } from '@/ts/Admin/Api/AdminFaq.type';

const props = defineProps<{
  tree: FaqTree;
  line: Extract<FaqTreeLine, { type: 'question' }>;
  category: AdminFaqTreeCategory;
  readOnly: boolean;
}>();

const { t, bootstrap } = useAdminContext();
const locales = bootstrap.locales;
const tabs = locales.map((code) => ({ id: code, label: localeName(code, bootstrap.locale) }));
const prefixes = Object.fromEntries(locales.map((code, index) => [code, `translations[${index}]`]));
const active = ref(bootstrap.locale);
const { data, errors, loading, saving, conflict } = props.line.resource;
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
const disabled = computed(() => !isNew && data.value?.disabled === true);
const offNote = computed(() => {
  if (disabled.value && props.category.disabled) {
    return {
      text: t('faq.question.off_both', { category: props.category.title }),
      action: t('faq.question.enable_both'),
    };
  }
  if (disabled.value) return { text: t('faq.question.off'), action: t('faq.question.enable') };
  if (props.category.disabled) return { text: t('faq.question.off_category', { category: props.category.title }) };
  return null;
});
</script>

<template>
  <UiForm
    tag="div"
    :errors="errors"
    id-prefix="faq-question"
    :aria-label="heading"
    class="gap-[22px]! rounded-lg bg-bg px-5 pt-[18px] pb-4"
    :class="readOnly ? 'shadow-[0_0_0_1px_var(--line-strong)]' : 'shadow-[0_0_0_2px_var(--primary)]'"
    @keydown.esc.stop="tree.cancelLine()"
  >
    <div class="flex flex-wrap items-center gap-x-3.5 gap-y-2.5">
      <p class="m-0 flex-[1_1_200px] text-md font-[650]">
        {{ heading }}
        <UiBadge v-if="disabled" tone="warning" class="ml-2">{{ t('faq.content.badge_disabled') }}</UiBadge>
      </p>
      <UiButton v-if="readOnly" size="sm" icon="close" :aria-label="t('faq.line.close')" @click="tree.cancelLine()" />
    </div>
    <UiSkeleton v-if="loading || !data" :lines="4" />
    <template v-else>
      <FaqLineOffNote
        v-if="offNote"
        :text="offNote.text"
        :action="readOnly ? undefined : offNote.action"
        :busy="tree.busy.value"
        @enable="tree.enableLine()"
      />
      <UiAlert v-if="conflict" variant="warning" :title="t('faq.line.conflict_title')">
        {{ t('faq.line.conflict_text') }}
        <template #action>
          <UiButton size="sm" icon="refresh" @click="line.resource.load()">{{ t('faq.line.reload') }}</UiButton>
        </template>
      </UiAlert>
      <UiTabs v-model="active" :tabs="tabs" :label="t('faq.line.languages')" :error-prefixes="prefixes">
        <template v-for="tab in tabs" :key="tab.id" #[tab.id]>
          <div class="flex flex-col gap-[18px]">
            <UiField
              :name="`translations[${locales.indexOf(tab.id)}].title`"
              :label="t('faq.question.title_label', { language: tab.label })"
              required
            >
              <UiInput
                :model-value="valueOf(tab.id, 'title')"
                :disabled="readOnly"
                @update:model-value="rowOf(tab.id).title = String($event ?? '')"
                @keydown.enter.prevent
              />
            </UiField>
            <UiField
              :name="`translations[${locales.indexOf(tab.id)}].answer`"
              :label="t('faq.question.answer_label', { language: tab.label })"
              required
            >
              <UiTextEditor
                :model-value="valueOf(tab.id, 'answer')"
                preset="full"
                :rows="6"
                :lang="tab.id"
                :readonly="readOnly"
                @update:model-value="rowOf(tab.id).answer = String($event ?? '')"
              />
            </UiField>
          </div>
        </template>
      </UiTabs>
    </template>
    <div v-if="!readOnly" class="flex flex-wrap items-center gap-2 border-t border-line pt-4">
      <span class="mr-auto flex items-center gap-1.5 text-sm text-fg-subtle" role="status">
        <span
          class="size-2 rounded-full"
          :class="tree.lineDirty.value ? 'bg-warning' : 'bg-success'"
          aria-hidden="true"
        ></span>
        {{ tree.lineDirty.value ? t('faq.line.dirty') : t('faq.line.clean') }}
      </span>
      <UiButton size="sm" :disabled="saving" @click="tree.cancelLine()">{{ t('faq.line.cancel') }}</UiButton>
      <UiButton
        size="sm"
        variant="primary"
        icon="check"
        :loading="saving"
        :disabled="loading || conflict"
        @click="tree.saveLine()"
      >
        {{ isNew ? t('faq.line.create') : t('faq.line.save') }}
      </UiButton>
    </div>
  </UiForm>
</template>

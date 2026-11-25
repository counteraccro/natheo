<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Catégorie ouverte dans l'arbre d'une FAQ : titre par langue (recopie de la langue de l'interface à la création),
 * erreurs de l'API, Entrée pour enregistrer et Échap pour annuler
 */
import { computed, ref } from 'vue';
import { UiAlert, UiBadge, UiButton, UiCheckbox, UiField, UiForm, UiInput, UiSkeleton, UiTabs } from '@ui';
import FaqLineOffNote from '@admin/Modules/Faq/FaqLineOffNote.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FaqTree, FaqTreeLine } from '@/vue/Composables/Admin/Faq/useFaqTree';
import type { AdminFaqCategory } from '@/ts/Admin/Api/AdminFaq.type';

const props = defineProps<{ tree: FaqTree; line: Extract<FaqTreeLine, { type: 'category' }> }>();

const { t, bootstrap } = useAdminContext();
const locales = bootstrap.locales;
const current = bootstrap.locale;
const currentName = localeName(current, bootstrap.locale);
const tabs = locales.map((code) => ({ id: code, label: localeName(code, bootstrap.locale) }));
const prefixes = Object.fromEntries(locales.map((code, index) => [code, `translations[${index}]`]));
const active = ref(current);
const { data, errors, loading, saving, conflict } = props.line.resource;
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
const disabled = computed(() => !isNew && data.value?.disabled === true);
</script>

<template>
  <UiForm
    tag="div"
    :errors="errors"
    id-prefix="faq-category"
    :aria-label="heading"
    class="gap-[22px]! rounded-lg bg-bg px-5 pt-[18px] pb-4 shadow-[0_0_0_2px_var(--primary)]"
    @keydown.esc.stop="tree.cancelLine()"
  >
    <div class="flex flex-wrap items-center gap-x-3.5 gap-y-2.5">
      <p class="m-0 flex-[1_1_200px] text-md font-[650]">
        {{ heading }}
        <UiBadge v-if="disabled" tone="warning" class="ml-2">{{ t('faq.content.badge_disabled') }}</UiBadge>
      </p>
    </div>
    <UiSkeleton v-if="loading || !data" :lines="2" />
    <template v-else>
      <FaqLineOffNote
        v-if="disabled"
        :text="t('faq.category.off')"
        :action="t('faq.category.enable')"
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
          <UiField
            :name="`translations[${locales.indexOf(tab.id)}].title`"
            :label="t('faq.category.title_label', { language: tab.label })"
            :help="isCopied(tab.id) ? t('faq.category.copied', { language: currentName }) : undefined"
            required
          >
            <UiInput
              :model-value="titleOf(tab.id)"
              :maxlength="255"
              :disabled="isCopied(tab.id)"
              @update:model-value="setTitle(tab.id, String($event ?? ''))"
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
      </UiTabs>
    </template>
    <div class="flex flex-wrap items-center gap-2 border-t border-line pt-4">
      <span class="mr-auto flex items-center gap-1.5 text-sm text-fg-subtle" role="status">
        <span
          class="size-2 rounded-full"
          :class="tree.lineDirty.value ? 'bg-warning' : 'bg-success'"
          aria-hidden="true"
        ></span>
        {{ tree.lineDirty.value ? t('faq.line.dirty') : t('faq.line.clean') }}
      </span>
      <UiButton size="sm" :disabled="saving" @click="tree.cancelLine()">{{ t('faq.line.cancel') }}</UiButton>
      <UiButton size="sm" variant="primary" icon="check" :loading="saving" :disabled="loading" @click="tree.saveLine()">
        {{ isNew ? t('faq.line.create') : t('faq.line.save') }}
      </UiButton>
    </div>
  </UiForm>
</template>

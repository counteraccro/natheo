<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu Natheo-Studio d'un écran de formulaire : sections, champs par langue en onglets, erreurs de l'API,
 * conflit d'enregistrement, panneau d'actions collé dans la colonne de droite (barre collée en bas sur écran étroit)
 * et informations de l'élément
 */
import { computed, ref, useId } from 'vue';
import {
  AdminPageHeader,
  UiAlert,
  UiButton,
  UiCheckbox,
  UiColorInput,
  UiErrorSummary,
  UiField,
  UiForm,
  UiInput,
  UiSelect,
  UiSkeleton,
  UiSwitch,
  UiTabs,
  UiTextarea,
} from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { toComponent } from '@/vue/Composables/Admin/Screen/adminTheme';
import { formatDateTime, formatRelative, localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { AdminScreenProps } from '@/ts/Admin/Theme/AdminTheme.type';
import type { FormField, FormScreenState, TranslationsField } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenAuthor, ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const props = defineProps<AdminScreenProps<FormScreenState<T>>>();

const { t, bootstrap } = useAdminContext();
const descriptor = props.screen.descriptor;
const { data, errors, loading, saving, conflict } = props.screen.resource;
const { isNew, title, canEdit, canDelete, copying, dirty } = props.screen;

const PANEL = 'rounded-lg border border-line bg-bg shadow-panel';
const PANEL_HEAD = 'flex items-center gap-3 border-b border-line px-5 py-4';
const PANEL_TITLE = 'm-0 text-md font-[650]';
const formId = useId();

const row = computed(() => (data.value ?? {}) as Record<string, unknown>);
const locales = props.screen.locales;
const tabs = locales.map((code) => ({ id: code, label: localeName(code, bootstrap.locale) }));
const currentLocaleName = localeName(props.screen.currentLocale, bootstrap.locale);
const activeTab = ref(props.screen.currentLocale);
const components = new Map(
  descriptor.sections
    .flatMap((section) => section.fields)
    .filter((field) => field.type === 'component')
    .map((field) => [field.name, toComponent(field.component)] as const)
);

const prefixesOf = (field: TranslationsField): Record<string, string> =>
  Object.fromEntries(locales.map((code, index) => [code, `${field.name}[${index}]`]));
const labelOf = (field: FormField): string => (field.label ? t(field.label) : '');
const helpOf = (field: FormField): string | undefined => (field.help ? t(field.help) : undefined);
const selectOptions = (field: Extract<FormField, { type: 'select' }>) =>
  field.options.map((option) => ({ value: option.value, label: t(option.label) }));

const meta = computed(() => {
  const item = row.value;
  return (descriptor.meta ?? []).map((name) => {
    if (name === 'author') {
      const author = item.author as ScreenAuthor | null | undefined;
      return {
        name,
        label: t('admin_app.screen.meta_author'),
        value: author?.login ?? t('admin_app.screen.meta_author_none'),
      };
    }
    if (name === 'pageCount') {
      return { name, label: t('admin_app.screen.meta_page_count'), value: String(item.pageCount ?? 0) };
    }
    const iso = item[name] as string | null | undefined;
    return {
      name,
      label: name === 'createdAt' ? t('admin_app.screen.meta_created_at') : t('admin_app.screen.meta_updated_at'),
      value: formatRelative(iso, bootstrap.locale),
      title: formatDateTime(iso, bootstrap.locale),
    };
  });
});
</script>

<template>
  <AdminPageHeader :title="title" :icon="descriptor.icon" />

  <div class="grid grid-cols-[minmax(0,1fr)_320px] items-start gap-5 max-xl:grid-cols-[minmax(0,1fr)]">
    <UiForm :id="formId" :errors="errors" @submit="screen.save()">
      <UiAlert v-if="conflict" variant="warning" :title="t('admin_app.resource.conflict_title')">
        {{ t('admin_app.resource.conflict_text') }}
        <template #action>
          <UiButton size="sm" icon="arrows-sort" @click="screen.reload()">{{
            t('admin_app.screen.form_reload')
          }}</UiButton>
        </template>
      </UiAlert>
      <UiAlert v-if="!loading && data && !canEdit" variant="info">{{ t('admin_app.screen.form_read_only') }}</UiAlert>
      <UiErrorSummary />

      <template v-if="loading || !data">
        <section v-for="section in descriptor.sections" :key="section.title" :class="PANEL" class="p-5">
          <UiSkeleton :lines="3" />
        </section>
      </template>

      <section v-for="section in descriptor.sections" v-else :key="section.title" :class="PANEL">
        <header :class="PANEL_HEAD">
          <h2 :class="PANEL_TITLE">{{ t(section.title) }}</h2>
        </header>
        <div class="flex flex-col gap-[18px] p-5">
          <template v-for="field in section.fields" :key="field.name">
            <UiTabs
              v-if="field.type === 'translations'"
              v-model="activeTab"
              :tabs="tabs"
              :label="t('admin_app.screen.translations_label')"
              :error-prefixes="prefixesOf(field)"
            >
              <template v-for="tab in tabs" :key="tab.id" #[tab.id]>
                <UiField
                  :name="screen.translationPath(field, tab.id)"
                  :label="`${labelOf(field)} (${tab.label})`"
                  :help="
                    screen.isCopied(field, tab.id)
                      ? t('admin_app.screen.translations_copied', { locale: currentLocaleName })
                      : helpOf(field)
                  "
                  required
                >
                  <UiInput
                    :model-value="screen.translation(field, tab.id)"
                    :maxlength="field.maxlength"
                    :counter="field.maxlength !== undefined"
                    :disabled="screen.isCopied(field, tab.id)"
                    @update:model-value="screen.setTranslation(field, tab.id, String($event ?? ''))"
                  />
                </UiField>
                <div v-if="field.copyFromCurrent && canEdit" class="mt-2.5">
                  <UiCheckbox
                    :model-value="copying"
                    :label="t('admin_app.screen.translations_copy', { locale: currentLocaleName })"
                    @update:model-value="screen.setCopying($event)"
                  />
                </div>
              </template>
            </UiTabs>
            <UiField v-else-if="field.type === 'text'" :name="field.name" :label="labelOf(field)" :help="helpOf(field)">
              <UiInput
                v-model="row[field.name] as string"
                :maxlength="field.maxlength"
                :counter="field.maxlength !== undefined"
              />
            </UiField>
            <UiField
              v-else-if="field.type === 'textarea'"
              :name="field.name"
              :label="labelOf(field)"
              :help="helpOf(field)"
            >
              <UiTextarea
                v-model="row[field.name] as string"
                :maxlength="field.maxlength"
                :counter="field.maxlength !== undefined"
              />
            </UiField>
            <UiField
              v-else-if="field.type === 'select'"
              :name="field.name"
              :label="labelOf(field)"
              :help="helpOf(field)"
            >
              <UiSelect v-model="row[field.name] as string" :options="selectOptions(field)" />
            </UiField>
            <UiField
              v-else-if="field.type === 'color'"
              :name="field.name"
              :label="labelOf(field)"
              :help="helpOf(field)"
            >
              <UiColorInput v-model="row[field.name] as string" :suggestions="field.suggestions" />
            </UiField>
            <UiSwitch
              v-else-if="field.type === 'switch'"
              v-model="row[field.name] as boolean"
              :label="labelOf(field)"
              :description="helpOf(field)"
            />
            <component
              :is="components.get(field.name)"
              v-else-if="field.type === 'component'"
              :data="row"
              :screen="screen"
            />
          </template>
        </div>
      </section>

      <div
        class="sticky bottom-4 z-20 flex flex-wrap items-center gap-2.5 rounded-lg bg-raised py-2.5 pr-3 pl-4 shadow-overlay xl:hidden"
      >
        <span v-if="canEdit" class="flex items-center gap-2 text-sm text-fg-muted">
          <span class="size-2 rounded-full" :class="dirty ? 'bg-warning' : 'bg-success'" aria-hidden="true"></span>
          {{ dirty ? t('admin_app.screen.form_dirty') : t('admin_app.screen.form_clean') }}
        </span>
        <div class="ml-auto flex gap-2">
          <UiButton
            v-if="canDelete"
            size="sm"
            variant="danger-soft"
            icon="trash"
            :disabled="saving"
            @click="screen.remove()"
          >
            {{ t('admin_app.screen.action_delete') }}
          </UiButton>
          <UiButton size="sm" icon="chevron-left" @click="screen.back()">{{
            t('admin_app.screen.form_back')
          }}</UiButton>
          <UiButton
            v-if="canEdit"
            type="submit"
            size="sm"
            variant="primary"
            icon="check"
            :loading="saving"
            :disabled="loading"
          >
            {{ isNew ? t('admin_app.screen.form_create') : t('admin_app.screen.form_save') }}
          </UiButton>
        </div>
      </div>
    </UiForm>

    <aside class="flex flex-col gap-5 xl:sticky xl:top-20">
      <section :class="PANEL" class="max-xl:hidden">
        <header v-if="canEdit" :class="PANEL_HEAD">
          <span class="size-2 rounded-full" :class="dirty ? 'bg-warning' : 'bg-success'" aria-hidden="true"></span>
          <p class="m-0 text-sm text-fg-muted">
            {{ dirty ? t('admin_app.screen.form_dirty') : t('admin_app.screen.form_clean') }}
          </p>
        </header>
        <div class="flex flex-col gap-2.5 p-5">
          <UiButton
            v-if="canEdit"
            type="submit"
            :form="formId"
            variant="primary"
            icon="check"
            class="w-full"
            :loading="saving"
            :disabled="loading"
          >
            {{ isNew ? t('admin_app.screen.form_create') : t('admin_app.screen.form_save') }}
          </UiButton>
          <UiButton icon="chevron-left" class="w-full" @click="screen.back()">
            {{ t('admin_app.screen.form_back') }}
          </UiButton>
          <template v-if="canDelete">
            <hr class="my-1.5 border-0 border-t border-line" />
            <UiButton variant="danger-soft" icon="trash" class="w-full" :disabled="saving" @click="screen.remove()">
              {{ t('admin_app.screen.action_delete') }}
            </UiButton>
          </template>
        </div>
      </section>

      <section v-if="!isNew && data && meta.length" :class="PANEL">
        <header :class="PANEL_HEAD">
          <h2 :class="PANEL_TITLE">{{ t('admin_app.screen.meta_title') }}</h2>
        </header>
        <dl class="m-0 flex flex-col gap-3 p-5 text-sm">
          <div v-for="item in meta" :key="item.name" class="flex justify-between gap-4">
            <dt class="text-fg-subtle">{{ item.label }}</dt>
            <dd class="m-0 font-semibold text-fg" :title="item.title">{{ item.value }}</dd>
          </div>
        </dl>
      </section>
    </aside>
  </div>
</template>

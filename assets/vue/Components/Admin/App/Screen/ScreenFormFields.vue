<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.3
 * Champs d'une section d'un écran de formulaire décrit par des données, communs aux thèmes : champs par langue en
 * onglets (langue partagée par les sections), texte, zone de texte, éditeur de texte, liste, couleur, interrupteur et
 * composant du module
 */
import { computed } from 'vue';
import { UiCheckbox, UiColorInput, UiField, UiInput, UiSelect, UiSwitch, UiTabs, UiTextarea, UiTextEditor } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { toComponent } from '@/vue/Composables/Admin/Screen/adminTheme';
import { localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FormField, FormScreenState, TranslationsField } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';
import type { AdminEditorKeyword } from '@/ts/Admin/Editor/AdminEditor.type';

const props = defineProps<{ screen: FormScreenState<T>; fields: FormField[] }>();

const { t, bootstrap } = useAdminContext();
const { data } = props.screen.resource;
const { canEdit, copying, activeLocale } = props.screen;

const row = computed(() => (data.value ?? {}) as Record<string, unknown>);
const locales = props.screen.locales;
const tabs = locales.map((code) => ({ id: code, label: localeName(code, bootstrap.locale) }));
const currentLocaleName = localeName(props.screen.currentLocale, bootstrap.locale);
const components = new Map(
  props.fields
    .filter((field) => field.type === 'component')
    .map((field) => [field.name, toComponent(field.component)] as const)
);

const prefixesOf = (field: TranslationsField): Record<string, string> =>
  Object.fromEntries(locales.map((code, index) => [code, `${field.name}[${index}]`]));
const labelOf = (field: FormField): string => (field.label ? t(field.label) : '');
const helpOf = (field: FormField): string | undefined => (field.help ? t(field.help) : undefined);
const placeholderOf = (field: { placeholder?: string }): string | undefined =>
  field.placeholder ? t(field.placeholder) : undefined;
const keywordsOf = (field: { keywords?: (data: Record<string, unknown>) => AdminEditorKeyword[] }) =>
  field.keywords?.(row.value) ?? [];
const selectOptions = (field: Extract<FormField, { type: 'select' }>) =>
  field.options.map((option) => ({ value: option.value, label: t(option.label) }));
</script>

<template>
  <template v-for="field in fields" :key="field.name">
    <UiTabs
      v-if="field.type === 'translations'"
      v-model="activeLocale"
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
          <UiTextEditor
            v-if="field.input === 'textEditor'"
            :model-value="screen.translation(field, tab.id)"
            :preset="field.preset"
            :rows="field.rows"
            :placeholder="placeholderOf(field)"
            :keywords="keywordsOf(field)"
            :lang="tab.id"
            :readonly="!canEdit"
            :disabled="canEdit && screen.isCopied(field, tab.id)"
            @update:model-value="screen.setTranslation(field, tab.id, String($event ?? ''))"
          />
          <UiInput
            v-else
            :model-value="screen.translation(field, tab.id)"
            :maxlength="field.maxlength"
            :counter="field.maxlength !== undefined"
            :disabled="!canEdit || screen.isCopied(field, tab.id)"
            @update:model-value="screen.setTranslation(field, tab.id, String($event ?? ''))"
          />
          <template v-if="field.copyFromCurrent && canEdit" #below>
            <UiCheckbox
              :model-value="copying"
              :label="t('admin_app.screen.translations_copy', { locale: currentLocaleName })"
              @update:model-value="screen.setCopying($event)"
            />
          </template>
        </UiField>
      </template>
    </UiTabs>
    <UiField v-else-if="field.type === 'text'" :name="field.name" :label="labelOf(field)" :help="helpOf(field)">
      <UiInput
        v-model="row[field.name] as string"
        :maxlength="field.maxlength"
        :counter="field.maxlength !== undefined"
        :disabled="!canEdit"
      />
    </UiField>
    <UiField v-else-if="field.type === 'textarea'" :name="field.name" :label="labelOf(field)" :help="helpOf(field)">
      <UiTextarea
        v-model="row[field.name] as string"
        :maxlength="field.maxlength"
        :counter="field.maxlength !== undefined"
        :disabled="!canEdit"
      />
    </UiField>
    <UiField v-else-if="field.type === 'textEditor'" :name="field.name" :label="labelOf(field)" :help="helpOf(field)">
      <UiTextEditor
        v-model="row[field.name] as string"
        :preset="field.preset"
        :rows="field.rows"
        :placeholder="placeholderOf(field)"
        :keywords="keywordsOf(field)"
        :readonly="!canEdit"
      />
    </UiField>
    <UiField v-else-if="field.type === 'select'" :name="field.name" :label="labelOf(field)" :help="helpOf(field)">
      <UiSelect v-model="row[field.name] as string" :options="selectOptions(field)" :disabled="!canEdit" />
    </UiField>
    <UiField v-else-if="field.type === 'color'" :name="field.name" :label="labelOf(field)">
      <UiColorInput
        v-model="row[field.name] as string"
        :suggestions="field.suggestions"
        :hint="helpOf(field)"
        :disabled="!canEdit"
      />
    </UiField>
    <UiSwitch
      v-else-if="field.type === 'switch'"
      v-model="row[field.name] as boolean"
      :label="labelOf(field)"
      :description="helpOf(field)"
      :disabled="!canEdit"
    />
    <component :is="components.get(field.name)" v-else-if="field.type === 'component'" :data="row" :screen="screen" />
  </template>
</template>

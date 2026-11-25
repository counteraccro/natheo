<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Rendu Natheo-Studio d'un écran de formulaire : sections, champs par langue en onglets, erreurs de l'API,
 * conflit d'enregistrement, panneau d'actions collé dans la colonne de droite (barre collée en bas sur écran étroit)
 * et informations de l'élément
 */
import { useId } from 'vue';
import { AdminPageHeader, UiAlert, UiButton, UiErrorSummary, UiForm, UiIcon, UiSkeleton } from '@ui';
import ScreenFormFields from '@admin/App/Screen/ScreenFormFields.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { SECTION_ICONS } from '@/vue/Composables/Admin/Screen/sectionKinds';
import { useFormMeta } from '@/vue/Composables/Admin/Screen/useFormMeta';
import type { AdminScreenProps } from '@/ts/Admin/Theme/AdminTheme.type';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const props = defineProps<AdminScreenProps<FormScreenState<T>>>();

const { t } = useAdminContext();
const descriptor = props.screen.descriptor;
const { data, errors, loading, saving, conflict } = props.screen.resource;
const { isNew, title, description, sections, canEdit, canDelete, dirty } = props.screen;

const PANEL = 'rounded-lg border border-line bg-bg shadow-panel';
const PANEL_HEAD = 'flex items-center gap-3 border-b border-line px-5 py-4';
const PANEL_TITLE = 'm-0 text-md font-[650]';
const formId = useId();

const meta = useFormMeta(props.screen);
</script>

<template>
  <AdminPageHeader :title="title" :description="description || undefined" :icon="descriptor.icon" />

  <div class="grid grid-cols-[minmax(0,1fr)_320px] items-start gap-5 max-xl:grid-cols-[minmax(0,1fr)]">
    <UiForm :id="formId" :errors="errors" @submit="screen.save()">
      <UiAlert v-if="conflict" variant="warning" :title="t('admin_app.resource.conflict_title')">
        {{ t('admin_app.resource.conflict_text') }}
        <template #action>
          <UiButton size="sm" icon="refresh" @click="screen.reload()">{{ t('admin_app.screen.form_reload') }}</UiButton>
        </template>
      </UiAlert>
      <UiAlert v-if="!loading && data && !canEdit" variant="info">{{ t('admin_app.screen.form_read_only') }}</UiAlert>
      <UiErrorSummary />

      <template v-if="loading || !data">
        <section v-for="section in sections" :key="section.title" :class="PANEL" class="p-5">
          <UiSkeleton :lines="3" />
        </section>
      </template>

      <section v-for="section in sections" v-else :key="section.title" :class="PANEL">
        <header :class="PANEL_HEAD">
          <span
            v-if="section.kind"
            class="grid size-8 shrink-0 place-items-center rounded-sm bg-surface text-fg-muted"
            aria-hidden="true"
          >
            <UiIcon :name="SECTION_ICONS[section.kind]" class="size-[18px]" />
          </span>
          <div class="min-w-0">
            <h2 :class="PANEL_TITLE">{{ t(section.title) }}</h2>
            <p v-if="section.description" class="m-0 mt-0.5 text-sm text-fg-subtle">{{ t(section.description) }}</p>
          </div>
        </header>
        <div class="flex flex-col gap-[18px] p-5">
          <ScreenFormFields :screen="screen" :fields="section.fields" />
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
        <div class="flex flex-col gap-3 px-5 py-4">
          <div class="flex gap-2">
            <UiButton size="sm" icon="chevron-left" class="flex-1" @click="screen.back()">
              {{ t('admin_app.screen.form_back') }}
            </UiButton>
            <UiButton
              v-if="canEdit"
              type="submit"
              :form="formId"
              size="sm"
              variant="primary"
              icon="check"
              class="flex-1"
              :loading="saving"
              :disabled="loading"
            >
              {{ isNew ? t('admin_app.screen.form_create') : t('admin_app.screen.form_save') }}
            </UiButton>
          </div>
          <template v-if="canDelete">
            <hr class="m-0 border-0 border-t border-line" />
            <UiButton
              size="sm"
              variant="danger-soft"
              icon="trash"
              class="w-full"
              :disabled="saving"
              @click="screen.remove()"
            >
              {{ t('admin_app.screen.action_delete') }}
            </UiButton>
          </template>
        </div>
      </section>

      <section v-if="!isNew && data && meta.length" :class="PANEL">
        <header :class="PANEL_HEAD">
          <span class="grid size-8 shrink-0 place-items-center rounded-sm bg-surface text-fg-muted" aria-hidden="true">
            <UiIcon :name="SECTION_ICONS.info" class="size-[18px]" />
          </span>
          <div class="min-w-0">
            <h2 :class="PANEL_TITLE">{{ t('admin_app.screen.meta_title') }}</h2>
            <p class="m-0 mt-0.5 text-sm text-fg-subtle">{{ t('admin_app.screen.meta_description') }}</p>
          </div>
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

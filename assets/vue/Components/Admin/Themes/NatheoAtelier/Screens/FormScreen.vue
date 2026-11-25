<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu Natheo-Atelier d'un écran de formulaire, dans le volet : actions en haut, sections séparées par des filets,
 * informations de l'élément en pied ; lien de retour à la liste sur écran étroit
 */
import { computed, useId } from 'vue';
import { useRouter } from 'vue-router';
import { UiAlert, UiButton, UiErrorSummary, UiForm, UiIcon, UiSkeleton } from '@ui';
import ScreenFormFields from '@admin/App/Screen/ScreenFormFields.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useFormMeta } from '@/vue/Composables/Admin/Screen/useFormMeta';
import { useScreenGroup } from '@/vue/Composables/Admin/Screen/useScreenGroup';
import type { AdminScreenProps } from '@/ts/Admin/Theme/AdminTheme.type';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const props = defineProps<AdminScreenProps<FormScreenState<T>>>();

const { t } = useAdminContext();
const router = useRouter();
const group = useScreenGroup();
const descriptor = props.screen.descriptor;
const { data, errors, loading, saving, conflict } = props.screen.resource;
const { isNew, title, description, sections, canEdit, canDelete, dirty } = props.screen;
const meta = useFormMeta(props.screen);
const formId = useId();
const backHref = computed(() => router.resolve({ name: descriptor.back }).href);
</script>

<template>
  <div class="max-w-[760px] px-10 pt-[26px] pb-10 max-md:p-5">
    <a
      :href="backHref"
      class="mb-3 hidden items-center gap-1.5 font-semibold text-fg-muted no-underline max-[860px]:inline-flex"
    >
      <UiIcon name="chevron-left" class="size-4" />
      {{ group ? t(group.descriptor.title) : t('admin_app.screen.form_back') }}
    </a>

    <div class="flex flex-wrap items-start gap-4 pb-5">
      <div class="min-w-0 flex-1 basis-[280px]">
        <h1 class="m-0 text-[22px] leading-tight font-[650] tracking-[-0.01em]">{{ title }}</h1>
        <p v-if="description" class="m-0 mt-1.5 max-w-[60ch] text-fg-muted">{{ description }}</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span v-if="canEdit" class="mr-1 inline-flex items-center gap-1.5 text-[13px] text-fg-subtle" role="status">
          <span class="size-2 rounded-full" :class="dirty ? 'bg-warning' : 'bg-success'" aria-hidden="true" />
          {{ dirty ? t('admin_app.screen.form_dirty') : t('admin_app.screen.form_clean') }}
        </span>
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
        <UiButton
          v-if="canEdit"
          type="submit"
          :form="formId"
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
        <div v-for="section in sections" :key="section.title" class="border-t border-line py-[22px]">
          <UiSkeleton :lines="3" />
        </div>
      </template>
      <section
        v-for="section in sections"
        v-else
        :key="section.title"
        class="grid grid-cols-[200px_minmax(0,1fr)] gap-6 border-t border-line py-[22px] max-[860px]:grid-cols-1 max-[860px]:gap-3"
      >
        <div>
          <h2 class="m-0 text-sm font-[650]">{{ t(section.title) }}</h2>
          <p v-if="section.description" class="m-0 mt-1 text-[13px] text-fg-subtle">{{ t(section.description) }}</p>
        </div>
        <div class="flex min-w-0 flex-col gap-4">
          <ScreenFormFields :screen="screen" :fields="section.fields" />
        </div>
      </section>
    </UiForm>

    <dl
      v-if="!isNew && data && meta.length"
      class="m-0 flex flex-wrap gap-x-8 gap-y-2 border-t border-line pt-[18px]"
      :aria-label="t('admin_app.screen.meta_title')"
    >
      <div v-for="item in meta" :key="item.name" class="grid gap-0.5">
        <dt class="text-[12.5px] text-fg-subtle">{{ item.label }}</dt>
        <dd class="m-0 text-[13.5px] font-semibold" :title="item.title">{{ item.value }}</dd>
      </div>
    </dl>
  </div>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Cadre d'une ligne ouverte dans l'arbre d'une FAQ : en-tête, rappel d'un élément désactivé, conflit, onglets des
 * langues (champs donnés par le slot de chaque langue) et pied avec l'état de la ligne ; Échap pour annuler
 */
import { computed, ref } from 'vue';
import { UiAlert, UiBadge, UiButton, UiForm, UiSkeleton, UiTabs } from '@ui';
import FaqLineOffNote from '@admin/Modules/Faq/FaqLineOffNote.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FaqTree, FaqTreeLine } from '@/vue/Composables/Admin/Faq/useFaqTree';

const props = withDefaults(
  defineProps<{
    tree: FaqTree;
    line: FaqTreeLine;
    heading: string;
    idPrefix: string;
    /** Lignes du squelette affiché pendant la lecture */
    skeletonLines: number;
    /** Rappel d'un élément désactivé, avec son bouton d'activation */
    offNote?: { text: string; action?: string } | null;
    readOnly?: boolean;
  }>(),
  { offNote: null, readOnly: false }
);

defineSlots<{ default(props: { locale: string; label: string; index: number }): unknown }>();

const { t, bootstrap } = useAdminContext();
const locales = bootstrap.locales;
const tabs = locales.map((code) => ({ id: code, label: localeName(code, bootstrap.locale) }));
const prefixes = Object.fromEntries(locales.map((code, index) => [code, `translations[${index}]`]));
const active = ref(bootstrap.locale);
const { data, errors, loading, saving, conflict } = props.line.resource;
const isNew = props.line.id === null;
const disabled = computed(() => !isNew && data.value?.disabled === true);
</script>

<template>
  <UiForm
    tag="div"
    :errors="errors"
    :id-prefix="idPrefix"
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
    <UiSkeleton v-if="loading || !data" :lines="skeletonLines" />
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
          <UiButton size="sm" icon="refresh" @click="tree.reloadLine()">{{ t('faq.line.reload') }}</UiButton>
        </template>
      </UiAlert>
      <UiTabs v-model="active" :tabs="tabs" :label="t('faq.line.languages')" :error-prefixes="prefixes">
        <template v-for="(tab, index) in tabs" :key="tab.id" #[tab.id]>
          <slot :locale="tab.id" :label="tab.label" :index="index" />
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

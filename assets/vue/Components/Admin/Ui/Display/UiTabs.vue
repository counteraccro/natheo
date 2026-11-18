<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Onglets (motif « tabs ») : focus itinérant, flèches, Début et Fin, activation par Entrée ou Espace ;
 * chaque panneau est monté à sa première ouverture (emplacement nommé par l'id de l'onglet) ; dans un UiForm,
 * errorPrefixes marque les onglets dont un champ est en erreur et les ouvre depuis le récapitulatif
 */
import { computed, nextTick, onBeforeUnmount, reactive, ref, useId, watch } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useFormContext } from '@/vue/Composables/Admin/Ui/formContext';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { TabItem } from '@/ts/Admin/Ui/Display/UiTabs.type';

const props = defineProps<{
  tabs: TabItem[];
  label: string;
  /** Préfixe des chemins de propriété de chaque onglet (fr → translations[fr]) */
  errorPrefixes?: Record<string, string>;
}>();
const active = defineModel<string>({ required: true });

const uid = useId();
const tabId = (id: string): string => `ui-tab-${uid}-${id}`;
const panelId = (id: string): string => `ui-tabpanel-${uid}-${id}`;
const focused = ref(active.value);
const list = ref<HTMLElement | null>(null);
const labels = useUiLabels();
const form = useFormContext();
// Dans un formulaire, tous les panneaux sont montés : chaque champ déclare son libellé au récapitulatif des erreurs
const visited = reactive(new Set<string>(form && props.errorPrefixes ? props.tabs.map((tab) => tab.id) : [active.value]));

const invalid = computed(
  () =>
    new Set(
      props.tabs
        .filter(
          (tab) =>
            tab.invalid ||
            (form && props.errorPrefixes?.[tab.id] && form.errors.hasErrorsIn(props.errorPrefixes[tab.id]))
        )
        .map((tab) => tab.id)
    )
);

// Ouvre l'onglet qui contient le champ visé par le récapitulatif des erreurs
const removeRevealer = form?.addRevealer((path) => {
  const tab = props.tabs.find((item) => {
    const prefix = props.errorPrefixes?.[item.id];
    return prefix && (path === prefix || path.startsWith(`${prefix}.`) || path.startsWith(`${prefix}[`));
  });
  if (!tab || tab.id === active.value) return false;
  active.value = tab.id;
  return true;
});
onBeforeUnmount(() => removeRevealer?.());

watch(active, (id) => {
  visited.add(id);
  focused.value = id;
});

const focusTab = async (index: number): Promise<void> => {
  const tab = props.tabs[(index + props.tabs.length) % props.tabs.length];
  focused.value = tab.id;
  await nextTick();
  list.value?.querySelector<HTMLElement>(`#${CSS.escape(tabId(tab.id))}`)?.focus();
};

const onKeydown = (event: KeyboardEvent): void => {
  const index = props.tabs.findIndex((tab) => tab.id === focused.value);
  const moves: Record<string, number> = { ArrowRight: index + 1, ArrowLeft: index - 1, Home: 0, End: -1 };
  if (event.key in moves) {
    event.preventDefault();
    focusTab(moves[event.key]);
  }
};
</script>

<template>
  <div class="flex flex-col gap-4">
    <div
      ref="list"
      role="tablist"
      :aria-label="label"
      class="flex gap-0.5 self-start rounded-md border border-line bg-surface p-[3px]"
      @keydown="onKeydown"
    >
      <button
        v-for="tab in tabs"
        :id="tabId(tab.id)"
        :key="tab.id"
        type="button"
        role="tab"
        :aria-selected="tab.id === active"
        :aria-controls="panelId(tab.id)"
        :tabindex="tab.id === focused ? 0 : -1"
        class="inline-flex h-8 cursor-pointer items-center gap-2 rounded-sm border-0 bg-transparent px-3 font-semibold text-fg-muted hover:text-fg aria-selected:bg-bg aria-selected:text-fg aria-selected:shadow-[0_1px_2px_rgb(17_17_20/.08),0_0_0_1px_var(--line)]"
        @click="active = tab.id"
        @focus="focused = tab.id"
      >
        <UiIcon v-if="tab.icon" :name="tab.icon" class="size-4" />
        <slot name="tab" :tab="tab">{{ tab.label }}</slot>
        <template v-if="invalid.has(tab.id)">
          <UiIcon name="exclamation-circle" class="size-3.5 text-danger" />
          <span class="sr-only">({{ labels.tabs.hasErrors }})</span>
        </template>
      </button>
    </div>
    <template v-for="tab in tabs" :key="tab.id">
      <div
        v-if="visited.has(tab.id)"
        v-show="tab.id === active"
        :id="panelId(tab.id)"
        role="tabpanel"
        :aria-labelledby="tabId(tab.id)"
        tabindex="0"
        class="focus-visible:rounded-md"
      >
        <slot :name="tab.id" />
      </div>
    </template>
  </div>
</template>

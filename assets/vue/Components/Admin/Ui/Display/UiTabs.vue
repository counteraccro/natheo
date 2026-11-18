<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Onglets (motif « tabs ») : focus itinérant, flèches, Début et Fin, activation par Entrée ou Espace ;
 * chaque panneau est monté à sa première ouverture (emplacement nommé par l'id de l'onglet)
 */
import { nextTick, reactive, ref, useId, watch } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import type { TabItem } from '@/ts/Admin/Ui/Display/UiTabs.type';

const props = defineProps<{ tabs: TabItem[]; label: string }>();
const active = defineModel<string>({ required: true });

const uid = useId();
const tabId = (id: string): string => `ui-tab-${uid}-${id}`;
const panelId = (id: string): string => `ui-tabpanel-${uid}-${id}`;
const focused = ref(active.value);
const visited = reactive(new Set<string>([active.value]));
const list = ref<HTMLElement | null>(null);

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

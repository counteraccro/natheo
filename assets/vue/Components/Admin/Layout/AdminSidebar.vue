<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sidebar du back-office : logo et bande de couleurs, arbre des entrées à N niveaux (dossiers de premier niveau en
 * groupes, dossier de l'entrée active ouvert), bloc utilisateur et version
 */
import { computed, ref, watch } from 'vue';
import BrandLogo from '@/vue/Components/Global/Ui/BrandLogo.vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import UiButton from '../Ui/Form/UiButton.vue';
import UiDropdown from '../Ui/Overlay/UiDropdown.vue';
import AdminSidebarTree from './AdminSidebarTree.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { SidebarItem, SidebarUser } from '@/ts/Admin/Layout/AdminSidebar.type';

const props = defineProps<{
  items: SidebarItem[];
  siteName: string;
  subtitle?: string;
  version?: string;
  user?: SidebarUser;
  /** Bouton de fermeture du panneau mobile */
  closable?: boolean;
}>();
const emit = defineEmits<{ close: []; navigate: [item: SidebarItem] }>();
defineSlots<{
  link?: (props: { item: SidebarItem; class: string; onClick: () => void }) => unknown;
  'user-menu'?: () => unknown;
}>();

const labels = useUiLabels();

/** Identifiants des dossiers qui mènent à l'entrée active, null si elle n'est pas dans ces entrées */
const activePath = (items: SidebarItem[]): string[] | null => {
  for (const item of items) {
    if (item.active) return [];
    const path = item.children ? activePath(item.children) : null;
    if (path) return [item.id, ...path];
  }
  return null;
};

const openIds = ref(new Set<string>());
watch(
  () => props.items,
  (items) => {
    openIds.value = new Set([...openIds.value, ...(activePath(items) ?? [])]);
  },
  { immediate: true }
);

const toggle = (id: string): void => {
  const next = new Set(openIds.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  openIds.value = next;
};

const groups = computed(() => props.items.map((item) => ({ item, group: !item.href && !!item.children?.length })));
</script>

<template>
  <aside class="flex h-full flex-col bg-side text-on-side-muted dark:border-r dark:border-side-line">
    <div class="flex items-center gap-3 px-5 pt-5 pb-4">
      <BrandLogo class="size-9 shrink-0" />
      <div class="min-w-0 flex-1">
        <b class="block truncate text-base leading-tight font-bold tracking-[-0.01em] text-on-side">{{ siteName }}</b>
        <small v-if="subtitle" class="block text-xs opacity-80">{{ subtitle }}</small>
      </div>
      <UiButton
        v-if="closable"
        variant="ghost"
        size="sm"
        icon="close"
        :aria-label="labels.sidebar.close"
        class="text-on-side-muted! hover:bg-side-hover! hover:text-on-side!"
        @click="emit('close')"
      />
    </div>
    <div class="mx-5 mb-2 grid h-[3px] grid-cols-[6fr_3fr_3fr_2fr_2fr_2fr] gap-[3px]" aria-hidden="true">
      <i class="rounded-xs bg-mod-full" />
      <i class="rounded-xs bg-mod-half" />
      <i class="rounded-xs bg-mod-half" />
      <i class="rounded-xs bg-mod-third-a" />
      <i class="rounded-xs bg-mod-third-a" />
      <i class="rounded-xs bg-mod-third-b" />
    </div>

    <nav
      :aria-label="labels.sidebar.nav"
      class="flex-1 overflow-y-auto px-3 pt-2 pb-4 [scrollbar-color:rgb(255_255_255/.2)_transparent] [scrollbar-width:thin]"
    >
      <template v-for="{ item, group } in groups" :key="item.id">
        <section v-if="group" class="mt-[18px]">
          <h2 class="m-0 px-2.5 pb-1.5 text-xs font-semibold text-on-side-subtle">{{ item.label }}</h2>
          <AdminSidebarTree
            :items="item.children!"
            :open-ids="openIds"
            :depth="0"
            @toggle="toggle"
            @navigate="emit('navigate', $event)"
          >
            <template v-if="$slots.link" #link="slotProps"><slot name="link" v-bind="slotProps" /></template>
          </AdminSidebarTree>
        </section>
        <AdminSidebarTree
          v-else
          :items="[item]"
          :open-ids="openIds"
          :depth="0"
          @toggle="toggle"
          @navigate="emit('navigate', $event)"
        >
          <template v-if="$slots.link" #link="slotProps"><slot name="link" v-bind="slotProps" /></template>
        </AdminSidebarTree>
      </template>
    </nav>

    <div v-if="user" class="mx-3 mb-3 flex items-center gap-2.5 rounded-md border border-side-line bg-side-card p-2.5">
      <span
        class="grid size-[34px] shrink-0 place-items-center rounded-full bg-primary-on-dark text-sm font-bold text-on-side-accent"
        aria-hidden="true"
      >
        {{ user.initials }}
      </span>
      <div class="min-w-0 flex-1">
        <b class="block truncate text-sm leading-tight font-semibold text-on-side">{{ user.name }}</b>
        <small v-if="user.detail" class="block truncate text-xs opacity-80">{{ user.detail }}</small>
      </div>
      <UiDropdown v-if="$slots['user-menu']" :label="labels.sidebar.userMenu" placement="top-end">
        <template #trigger="{ props: triggerProps, setRef }">
          <button
            :ref="setRef"
            v-bind="triggerProps"
            type="button"
            :aria-label="labels.sidebar.userMenu"
            class="grid size-8 cursor-pointer place-items-center rounded-sm border-0 bg-transparent text-inherit hover:bg-side-hover hover:text-on-side focus-visible:outline-primary-on-dark aria-expanded:bg-side-active aria-expanded:text-on-side"
          >
            <UiIcon name="dots-vertical" class="size-5" />
          </button>
        </template>
        <slot name="user-menu" />
      </UiDropdown>
    </div>
    <p v-if="version" class="m-0 px-6 pb-3.5 text-2xs text-on-side-subtle">{{ labels.sidebar.version(version) }}</p>
  </aside>
</template>

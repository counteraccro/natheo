<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Layout du thème Natheo-Atelier : barre du haut (sections du menu et leurs compteurs, palette de commandes, « Nouveau », apparence,
 * notifications, menu utilisateur), onglets des modules de la section, établi et barre de debug en dev
 */
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
  AdminAppearanceMenu,
  AdminDebugBar,
  AdminUserMenu,
  BrandLogo,
  UiButton,
  UiDropdown,
  UiDropdownItem,
  UiIcon,
  useUiLabels,
  type SidebarItem,
} from '@ui';
import AtelierCommandPalette from '@admin/Themes/NatheoAtelier/AtelierCommandPalette.vue';
import AtelierList from '@admin/Themes/NatheoAtelier/AtelierList.vue';
import { ATELIER_PANE_TOP } from '@admin/Themes/NatheoAtelier/atelier';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useLayoutActions } from '@/vue/Composables/Admin/Layout/useLayoutActions';
import { useScreenGroup } from '@/vue/Composables/Admin/Screen/useScreenGroup';
import type { AdminLayoutProps } from '@/ts/Admin/Theme/AdminTheme.type';
import { isMac } from '@/vue/Composables/Global/isMac';

const props = defineProps<AdminLayoutProps>();
defineSlots<{ default?: () => unknown }>();

const { t } = useAdminContext();
const labels = useUiLabels();
const actions = useLayoutActions(props.bootstrap);
const { impersonating, debugSections, debugUsers, themeChoice } = actions;

const mac = isMac();
const shortcutKey = mac ? { label: '⌘ K', aria: 'Meta+K' } : { label: 'Ctrl K', aria: 'Control+K' };
const paletteOpen = ref(false);
const group = useScreenGroup();

/**
 * Entrée courante ou ancêtre de l'entrée courante
 * @param item
 */
const isActive = (item: SidebarItem): boolean => item.active === true || (item.children ?? []).some(isActive);

/**
 * Compteur d'une entrée, ou somme de ceux de ses enfants (une section ne porte pas le sien)
 * @param item
 */
const badgeOf = (item: SidebarItem): number =>
  item.badge ?? (item.children ?? []).reduce((total, child) => total + badgeOf(child), 0);

const activeSection = computed(() => props.items.find(isActive) ?? null);
const selectedId = ref<string | null>(activeSection.value?.id ?? null);
watch(activeSection, (section) => (selectedId.value = section?.id ?? null));
const selected = computed(() => props.items.find((item) => item.id === selectedId.value) ?? null);
const modules = computed(() => selected.value?.children ?? []);

/**
 * Section à liens : affiche ses modules ; section sans enfant : ouvre sa page
 * @param item
 */
const selectSection = (item: SidebarItem): void => {
  if (item.children?.length) {
    selectedId.value = item.id;
  } else if (item.href) {
    actions.open(item.href);
  }
};

const onKeydown = (event: KeyboardEvent): void => {
  if ((mac ? event.metaKey : event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    paletteOpen.value = true;
  }
};
onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
  <div class="flex h-dvh flex-col bg-bg">
    <div class="z-30 shrink-0 bg-bg">
      <div
        v-if="impersonating"
        class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 bg-warning-soft px-4 py-1.5 text-sm text-warning"
        role="status"
      >
        <UiIcon name="user" class="size-4" />
        <span>{{ labels.header.impersonation(impersonating.name) }}</span>
        <a :href="impersonating.exitHref" class="font-semibold text-inherit underline underline-offset-3">
          {{ labels.header.impersonationExit }}
        </a>
      </div>

      <header
        class="grid h-14 grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-5 pr-4 pl-[18px] max-[1100px]:grid-cols-[auto_minmax(0,1fr)_auto] max-md:gap-2 max-md:px-2.5"
      >
        <a
          :href="bootstrap.urls.dashboard"
          class="ui-ring flex min-w-0 items-center gap-2.5 rounded-md text-[15px] font-bold tracking-[-0.01em] text-fg no-underline"
        >
          <BrandLogo class="size-[26px] shrink-0" />
          <span class="truncate max-md:sr-only">{{ bootstrap.site.name }}</span>
        </a>

        <nav :aria-label="t('admin_app.atelier.sections')" class="flex gap-0.5 max-[1100px]:hidden">
          <button
            v-for="item in items"
            :key="item.id"
            type="button"
            :aria-pressed="item.children?.length ? item.id === selectedId : undefined"
            :aria-current="!item.children?.length && item.active ? 'page' : undefined"
            class="ui-ring inline-flex h-[34px] cursor-pointer items-center gap-1.5 rounded-lg border-0 bg-transparent px-3 font-[550] text-fg-muted hover:bg-surface hover:text-fg aria-pressed:bg-surface aria-pressed:text-fg aria-[current=page]:bg-surface aria-[current=page]:text-fg"
            @click="selectSection(item)"
          >
            {{ item.label }}
            <span
              v-if="badgeOf(item)"
              class="grid h-[18px] min-w-[18px] place-items-center rounded-full bg-primary px-1.5 text-[11px] leading-none font-bold text-on-primary tabular-nums"
              >{{ badgeOf(item) }}</span
            >
          </button>
        </nav>

        <button
          type="button"
          aria-haspopup="dialog"
          :aria-keyshortcuts="shortcutKey.aria"
          :aria-label="t('admin_app.atelier.command')"
          class="ui-ring flex h-9 w-[min(440px,100%)] cursor-pointer items-center gap-2.5 justify-self-center rounded-[10px] border border-line-strong bg-canvas pr-2 pl-3 text-left text-fg-subtle hover:border-field max-md:w-9 max-md:justify-center max-md:justify-self-end max-md:border-0 max-md:bg-transparent max-md:p-0"
          @click="paletteOpen = true"
        >
          <UiIcon name="search" class="size-4 shrink-0" />
          <span class="flex-1 truncate max-md:hidden">{{ t('admin_app.atelier.command') }}</span>
          <kbd
            class="rounded-[5px] border border-b-2 border-line-strong bg-bg px-1.5 font-sans text-xs font-semibold text-fg-muted max-md:hidden"
            aria-hidden="true"
            >{{ shortcutKey.label }}</kbd
          >
        </button>

        <div class="flex items-center justify-end gap-1">
          <UiDropdown
            v-if="shortcuts.length"
            :label="labels.header.new"
            icon="plus"
            size="sm"
            variant="primary"
            class="max-md:hidden"
          >
            <UiDropdownItem v-for="shortcut in shortcuts" :key="shortcut.id" :href="shortcut.href">
              {{ shortcut.label }}
            </UiDropdownItem>
          </UiDropdown>
          <AdminAppearanceMenu
            :layouts="bootstrap.themes"
            :layout="bootstrap.theme"
            @select-layout="themeChoice.choose"
          />
          <UiButton
            v-if="bootstrap.options.notifications"
            variant="ghost"
            icon="bell"
            :aria-label="labels.header.notifications"
            @click="actions.openNotifications()"
          />
          <UiDropdown :label="labels.sidebar.userMenu">
            <template #trigger="{ props: triggerProps, setRef }">
              <button
                :ref="setRef"
                v-bind="triggerProps"
                type="button"
                :aria-label="labels.sidebar.userMenu"
                class="ui-ring ml-1.5 size-8 cursor-pointer rounded-full border-0 bg-primary-soft p-0 text-xs font-bold text-on-primary-soft"
              >
                {{ bootstrap.user.initials }}
              </button>
            </template>
            <AdminUserMenu />
          </UiDropdown>
        </div>
      </header>

      <nav
        v-if="modules.length"
        :aria-label="selected?.label"
        class="flex h-[42px] items-end gap-1 overflow-x-auto border-b border-line px-[18px]"
      >
        <template v-for="module in modules" :key="module.id">
          <a
            v-if="module.href && !module.children?.length"
            :href="module.href"
            :aria-current="module.active ? 'page' : undefined"
            class="atelier-tab"
          >
            <UiIcon v-if="module.icon" :path="module.icon" class="size-4 shrink-0" />
            {{ module.label }}
            <span
              v-if="badgeOf(module)"
              class="grid h-[18px] min-w-[18px] place-items-center rounded-full bg-primary px-1.5 text-[11px] leading-none font-bold text-on-primary tabular-nums"
              >{{ badgeOf(module) }}</span
            >
          </a>
          <UiDropdown v-else :label="module.label" variant="ghost" size="sm" class="h-full">
            <template #trigger="{ props: triggerProps, setRef }">
              <button
                :ref="setRef"
                v-bind="triggerProps"
                type="button"
                class="atelier-tab cursor-pointer border-0 bg-transparent"
                :data-active="isActive(module) || undefined"
              >
                <UiIcon v-if="module.icon" :path="module.icon" class="size-4 shrink-0" />
                {{ module.label }}
                <span
                  v-if="badgeOf(module)"
                  class="grid h-[18px] min-w-[18px] place-items-center rounded-full bg-primary px-1.5 text-[11px] leading-none font-bold text-on-primary tabular-nums"
                  >{{ badgeOf(module) }}</span
                >
                <UiIcon name="chevron-down" class="size-3.5" />
              </button>
            </template>
            <UiDropdownItem
              v-for="child in module.children ?? []"
              :key="child.id"
              :href="child.href"
              :icon-path="child.icon"
              :aria-current="child.active ? 'page' : undefined"
            >
              <span class="flex items-center gap-2">
                <span class="flex-1">{{ child.label }}</span>
                <span
                  v-if="badgeOf(child)"
                  class="grid h-[18px] min-w-[18px] place-items-center rounded-full bg-primary px-1.5 text-[11px] leading-none font-bold text-on-primary tabular-nums"
                  >{{ badgeOf(child) }}</span
                >
              </span>
            </UiDropdownItem>
          </UiDropdown>
        </template>
      </nav>
      <div v-else class="border-b border-line" />
    </div>

    <main
      v-if="group"
      class="grid min-h-0 flex-1 grid-cols-[360px_minmax(0,1fr)] grid-rows-[minmax(0,1fr)] max-[860px]:grid-cols-1"
    >
      <AtelierList
        :key="group.name"
        :descriptor="group.descriptor"
        :class="{ 'max-[860px]:hidden': !group.isCollection }"
      />
      <div class="min-h-0 min-w-0 overflow-auto bg-bg" :class="{ 'max-[860px]:hidden': group.isCollection }">
        <div :id="ATELIER_PANE_TOP" />
        <div aria-live="polite"><slot /></div>
      </div>
    </main>
    <main v-else class="min-h-0 flex-1 overflow-auto">
      <div class="flex flex-col gap-5 px-[18px] pt-6 pb-14 max-md:px-2.5 max-md:pt-4">
        <slot />
      </div>
    </main>

    <AtelierCommandPalette
      v-model:open="paletteOpen"
      :items="items"
      :shortcuts="shortcuts"
      @open-href="actions.open"
      @search="actions.submitSearch"
    />

    <AdminDebugBar
      v-if="bootstrap.debug"
      :env="bootstrap.debug.env"
      :branch="bootstrap.debug.branch ?? undefined"
      :sections="debugSections"
      :users="debugUsers"
    />
  </div>
</template>

<style scoped>
/* Onglet d'un module : soulignement de la couleur principale sur la page courante */
.atelier-tab {
  position: relative;
  display: flex;
  align-items: center;
  gap: 6px;
  height: 100%;
  padding: 0 10px;
  color: var(--fg-muted);
  font-weight: 550;
  text-decoration: none;
  white-space: nowrap;
}
.atelier-tab:hover,
.atelier-tab[aria-current='page'],
.atelier-tab[data-active] {
  color: var(--fg);
}
.atelier-tab[aria-current='page']::after,
.atelier-tab[data-active]::after {
  content: '';
  position: absolute;
  right: 10px;
  bottom: 0;
  left: 10px;
  height: 2px;
  border-radius: 2px;
  background: var(--primary);
}
.atelier-tab:focus-visible {
  outline: 2px solid var(--focus);
  outline-offset: -2px;
  border-radius: 6px;
}
</style>

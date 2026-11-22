<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Barre du haut en trois zones : section courante, recherche globale, actions (Nouveau, apparence, notifications,
 * menu utilisateur) ; bandeau d'usurpation au-dessus
 */
import { onBeforeUnmount, onMounted, ref, useId } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import UiButton from '@admin/Ui/Form/UiButton.vue';
import UiDropdown from '@admin/Ui/Overlay/UiDropdown.vue';
import UiDropdownItem from '@admin/Ui/Overlay/UiDropdownItem.vue';
import AdminAppearanceMenu from '@admin/Layout/AdminAppearanceMenu.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { HeaderSection, HeaderShortcut, LayoutChoice } from '@/ts/Admin/Layout/AdminHeader.type';

withDefaults(
  defineProps<{
    section?: HeaderSection;
    shortcuts?: HeaderShortcut[];
    notifications?: number;
    userInitials?: string;
    /** Nom de l'utilisateur usurpé, et lien pour revenir à son compte */
    impersonating?: { name: string; exitHref: string };
    searchable?: boolean;
    /** Cloche des notifications (option OS_NOTIFICATION) */
    notifiable?: boolean;
    /** Dispositions du menu Apparence et celle de l'utilisateur */
    layouts?: LayoutChoice[];
    layout?: string;
  }>(),
  { searchable: true, notifiable: true, layouts: () => [], layout: '' }
);
const search = defineModel<string>('search', { default: '' });
const emit = defineEmits<{
  openNav: [];
  submitSearch: [term: string];
  openNotifications: [];
  selectLayout: [id: string];
}>();
defineSlots<{ 'user-menu'?: () => unknown; actions?: () => unknown }>();

const labels = useUiLabels();
const searchId = `ui-global-search-${useId()}`;
const searchInput = ref<HTMLInputElement | null>(null);
const isMac = /Mac|iPhone|iPad/.test(navigator.userAgent);
const shortcut = isMac ? { label: '⌘ K', aria: 'Meta+K' } : { label: 'Ctrl K', aria: 'Control+K' };

const focusSearch = (event: KeyboardEvent): void => {
  if (!(isMac ? event.metaKey : event.ctrlKey) || event.key.toLowerCase() !== 'k') return;
  // Champ masqué sur mobile : le raccourci garde alors son comportement par défaut
  const form = searchInput.value?.closest('form');
  if (!form || getComputedStyle(form).display === 'none') return;
  event.preventDefault();
  searchInput.value?.focus();
};
onMounted(() => window.addEventListener('keydown', focusSearch));
onBeforeUnmount(() => window.removeEventListener('keydown', focusSearch));
</script>

<template>
  <div class="sticky top-0 z-30">
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
      class="relative isolate grid h-[60px] grid-cols-[minmax(0,1fr)_minmax(0,440px)_minmax(0,1fr)] items-center gap-4 border-b border-line px-7 before:absolute before:inset-0 before:-z-10 before:bg-[color-mix(in_srgb,var(--bg)_88%,transparent)] before:backdrop-blur-[10px] before:content-[''] max-lg:px-4 max-md:grid-cols-[minmax(0,1fr)_auto]"
    >
      <div class="flex min-w-0 items-center gap-2 overflow-hidden text-sm whitespace-nowrap text-fg-subtle">
        <UiButton
          variant="ghost"
          icon="bars"
          data-nav-toggle
          :aria-label="labels.header.openMenu"
          class="lg:hidden"
          @click="emit('openNav')"
        />
        <template v-if="section">
          <template v-if="section.group">
            <span class="max-md:hidden">{{ section.group }}</span>
            <span class="h-4 w-px bg-line-strong max-md:hidden" aria-hidden="true" />
          </template>
          <span
            v-if="section.icon || section.iconName"
            class="-mr-0.5 grid size-[26px] shrink-0 place-items-center rounded-sm bg-primary-soft text-on-primary-soft"
          >
            <UiIcon :name="section.iconName" :path="section.icon" class="size-4" />
          </span>
          <b class="truncate text-base font-semibold text-fg">{{ section.label }}</b>
        </template>
      </div>

      <form
        v-if="searchable"
        role="search"
        class="relative max-md:hidden"
        @submit.prevent="emit('submitSearch', search)"
      >
        <label :for="searchId" class="sr-only">{{ labels.header.searchLabel }}</label>
        <UiIcon
          name="search"
          class="pointer-events-none absolute top-1/2 left-[11px] size-4 -translate-y-1/2 text-fg-subtle"
        />
        <input
          :id="searchId"
          ref="searchInput"
          v-model="search"
          :aria-keyshortcuts="shortcut.aria"
          type="search"
          :placeholder="labels.header.searchPlaceholder"
          class="h-[38px] w-full rounded-md border border-line-strong bg-bg pr-16 pl-9 text-fg transition-[border-color,box-shadow] duration-100 placeholder:text-fg-subtle hover:border-field focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--primary)_18%,transparent)] focus:outline-none"
        />
        <kbd
          class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2 rounded-[4px] border border-line-strong bg-surface px-1.5 py-px font-sans text-2xs text-fg-subtle"
          aria-hidden="true"
          >{{ shortcut.label }}</kbd
        >
      </form>
      <span v-else class="max-md:hidden" />

      <div class="flex items-center justify-end gap-2">
        <UiDropdown v-if="shortcuts?.length" :label="labels.header.new" icon="plus" size="sm">
          <UiDropdownItem v-for="shortcut in shortcuts" :key="shortcut.id" :href="shortcut.href">
            {{ shortcut.label }}
          </UiDropdownItem>
        </UiDropdown>
        <slot name="actions" />
        <AdminAppearanceMenu :layouts="layouts" :layout="layout" @select-layout="emit('selectLayout', $event)" />
        <span v-if="notifiable" class="relative inline-flex">
          <UiButton
            variant="ghost"
            icon="bell"
            :aria-label="labels.header.notifications"
            @click="emit('openNotifications')"
          />
          <span
            v-if="notifications"
            class="pointer-events-none absolute top-1 right-1 grid h-4 min-w-4 place-items-center rounded-full bg-primary px-1 text-[10px] font-bold text-on-primary"
            aria-hidden="true"
          >
            {{ notifications }}
          </span>
        </span>
        <UiDropdown v-if="$slots['user-menu'] && userInitials" :label="labels.sidebar.userMenu">
          <template #trigger="{ props: triggerProps, setRef }">
            <button
              :ref="setRef"
              v-bind="triggerProps"
              type="button"
              :aria-label="labels.sidebar.userMenu"
              class="ui-ring size-9 cursor-pointer rounded-full border-0 bg-primary-soft p-0 text-sm font-bold text-on-primary-soft aria-expanded:shadow-[0_0_0_3px_color-mix(in_srgb,var(--primary)_25%,transparent)]"
            >
              {{ userInitials }}
            </button>
          </template>
          <slot name="user-menu" />
        </UiDropdown>
      </div>
    </header>
  </div>
</template>

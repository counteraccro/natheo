<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Sélecteur d'apparence de la barre du haut : disposition (thème du back-office et sa miniature), miniatures du
 * back-office en clair et en sombre à la couleur choisie, puis pastilles en blocs des couleurs ; le panneau reste
 * ouvert pour comparer
 */
import { computed, useId } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import UiDropdown from '@admin/Ui/Overlay/UiDropdown.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { useAppearance } from '@/vue/Composables/Admin/Ui/useAppearance';
import { themeThumbnail } from '@/vue/Composables/Admin/loadAdminTheme';
import type { ColorMode, LayoutChoice } from '@/ts/Admin/Layout/AdminHeader.type';

withDefaults(
  defineProps<{
    /** Dispositions proposées, rubrique masquée s'il y en a moins de deux */
    layouts?: LayoutChoice[];
    /** Disposition de l'utilisateur */
    layout?: string;
  }>(),
  { layouts: () => [], layout: '' }
);
const emit = defineEmits<{ selectLayout: [id: string] }>();

const labels = useUiLabels();
const layoutLabelId = `ui-appearance-layout-${useId()}`;
const { theme, mode, themes, setTheme, setMode } = useAppearance();
const colorLabelId = `ui-appearance-color-${useId()}`;

const modes = computed(() => [
  {
    value: 'light' as ColorMode,
    label: labels.header.modeLight,
    icon: 'sun' as const,
    accent: `var(--theme-${theme.value})`,
    tile: 'bg-preview-light-canvas',
    side: 'bg-preview-light-side',
    surface: 'bg-preview-light-bg border-preview-light-bar',
    bar: 'bg-preview-light-bar',
  },
  {
    value: 'dark' as ColorMode,
    label: labels.header.modeDark,
    icon: 'moon' as const,
    accent: `var(--theme-${theme.value}-tint)`,
    tile: 'bg-preview-dark-canvas',
    side: 'bg-preview-dark-side',
    surface: 'bg-preview-dark-bg border-preview-dark-bar',
    bar: 'bg-preview-dark-bar',
  },
]);

/**
 * Flèches gauche et droite : élément voisin du panneau, les flèches haut et bas restant au menu
 * @param event
 */
const moveFocus = (event: KeyboardEvent): void => {
  const delta = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
  const menu = (event.currentTarget as HTMLElement).closest('[role="menu"]');
  if (!delta || !menu) return;
  event.preventDefault();
  const items = [...menu.querySelectorAll<HTMLElement>('[role="menuitemradio"]')];
  const index = items.indexOf(document.activeElement as HTMLElement);
  items[(index + delta + items.length) % items.length]?.focus();
};
</script>

<template>
  <UiDropdown
    :label="labels.header.appearance"
    :icon="mode === 'dark' ? 'moon' : 'sun'"
    icon-only
    variant="ghost"
    width="w-[300px]"
  >
    <div class="p-1.5" @keydown="moveFocus">
      <template v-if="layouts.length > 1">
        <div class="px-1">
          <span :id="layoutLabelId" class="text-sm font-medium">{{ labels.header.layout }}</span>
          <p class="mt-0.5 text-xs text-fg-muted">{{ labels.header.layoutHelp }}</p>
        </div>
        <div role="group" :aria-labelledby="layoutLabelId" class="mt-1.5 mb-3 grid grid-cols-2 gap-2">
          <button
            v-for="option in layouts"
            :key="option.id"
            type="button"
            role="menuitemradio"
            tabindex="-1"
            :aria-checked="layout === option.id"
            class="ui-ring group cursor-pointer rounded-md border-0 bg-transparent p-1 text-left text-fg hover:bg-surface focus:bg-surface focus:outline-none"
            @click="emit('selectLayout', option.id)"
            @keydown.space.prevent="emit('selectLayout', option.id)"
          >
            <span
              aria-hidden="true"
              class="relative block h-[64px] overflow-hidden rounded-[6px] border border-line-strong bg-canvas transition-[border-color,box-shadow] duration-150 group-aria-checked:border-primary group-aria-checked:shadow-[0_0_0_1px_var(--primary)]"
            >
              <component :is="themeThumbnail(option.id)" v-if="themeThumbnail(option.id)" />
              <span v-else class="absolute inset-x-0 top-0 h-[12px] border-b border-line bg-bg" />
            </span>
            <span class="mt-1.5 flex items-center gap-1.5 px-0.5 text-sm">
              <span class="flex-1 truncate">{{ option.label }}</span>
              <UiIcon name="check" class="size-4 text-primary invisible group-aria-checked:visible" />
            </span>
          </button>
        </div>
      </template>
      <div role="group" :aria-label="labels.header.mode" class="grid grid-cols-2 gap-2">
        <button
          v-for="option in modes"
          :key="option.value"
          type="button"
          role="menuitemradio"
          tabindex="-1"
          :aria-checked="mode === option.value"
          class="ui-ring group cursor-pointer rounded-md border-0 bg-transparent p-1 text-left text-fg hover:bg-surface focus:bg-surface focus:outline-none"
          @click="setMode(option.value)"
          @keydown.space.prevent="setMode(option.value)"
        >
          <span
            aria-hidden="true"
            class="relative block h-[78px] overflow-hidden rounded-[6px] border border-line-strong transition-[border-color,box-shadow] duration-150 group-aria-checked:border-primary group-aria-checked:shadow-[0_0_0_1px_var(--primary)]"
            :class="option.tile"
          >
            <span
              class="absolute inset-y-0 left-0 flex w-[26%] flex-col gap-[5px] px-[5px] pt-[7px]"
              :class="option.side"
            >
              <span class="h-[5px] w-3/4 rounded-[2px]" :style="{ background: option.accent }" />
              <span class="h-[3px] rounded-full bg-preview-side-bar" />
              <span class="h-[3px] w-4/5 rounded-full bg-preview-side-bar" />
              <span class="h-[3px] w-3/5 rounded-full bg-preview-side-bar" />
            </span>
            <span
              class="absolute top-0 right-0 left-[26%] flex h-[13px] items-center justify-end gap-[3px] border-b px-1.5"
              :class="option.surface"
            >
              <span class="size-[5px] rounded-full" :class="option.bar" />
              <span class="size-[5px] rounded-full" :style="{ background: option.accent }" />
            </span>
            <span
              class="absolute top-[21px] right-[7px] bottom-[7px] left-[calc(26%+7px)] flex flex-col gap-[4px] rounded-[3px] border p-[6px]"
              :class="option.surface"
            >
              <span class="h-[3px] w-4/5 rounded-full" :class="option.bar" />
              <span class="h-[3px] w-1/2 rounded-full" :class="option.bar" />
              <span class="mt-auto h-[7px] w-[38%] rounded-[2px]" :style="{ background: option.accent }" />
            </span>
          </span>
          <span class="mt-1.5 flex items-center gap-1.5 px-0.5 text-sm">
            <UiIcon :name="option.icon" class="size-4 text-fg-subtle" />
            <span class="flex-1">{{ option.label }}</span>
            <UiIcon name="check" class="size-4 text-primary invisible group-aria-checked:visible" />
          </span>
        </button>
      </div>

      <div class="mt-3 flex items-baseline justify-between px-1 text-sm">
        <span :id="colorLabelId" class="font-medium">{{ labels.header.color }}</span>
        <span class="text-fg-muted">{{ labels.header.themes[theme] }}</span>
      </div>
      <div role="group" :aria-labelledby="colorLabelId" class="mt-1.5 grid grid-cols-4 gap-2">
        <button
          v-for="color in themes"
          :key="color"
          type="button"
          role="menuitemradio"
          tabindex="-1"
          :aria-checked="theme === color"
          class="ui-ring grid h-12 cursor-pointer place-items-center rounded-md border border-line-strong bg-transparent p-0 transition-[border-color,box-shadow] duration-150 hover:border-field focus:outline-none aria-checked:border-primary aria-checked:shadow-[0_0_0_1px_var(--primary)]"
          @click="setTheme(color)"
          @keydown.space.prevent="setTheme(color)"
        >
          <span aria-hidden="true" class="grid size-7 grid-cols-2 grid-rows-2 gap-[3px]">
            <span class="col-span-2 rounded-[3px]" :style="{ background: `var(--theme-${color}-tint)` }" />
            <span class="rounded-[3px]" :style="{ background: `var(--theme-${color})` }" />
            <span class="rounded-[3px]" :style="{ background: `var(--theme-${color})` }" />
          </span>
          <span class="sr-only">{{ labels.header.themes[color] }}</span>
        </button>
      </div>
    </div>
  </UiDropdown>
</template>

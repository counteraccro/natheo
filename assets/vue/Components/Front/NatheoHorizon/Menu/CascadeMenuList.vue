<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste d'un menu déroulant dont chaque sous-niveau s'ouvre dans un panneau à côté : survol à la souris,
 * bouton chevron au clic ou au doigt, flèches droite / gauche au clavier
 */
import { inject, nextTick, ref, watch } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { isMouse, useHoverDelay } from '@/vue/Composables/Front/useHoverDelay';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { NAV_PANEL_OPEN } from '@/vue/Components/Front/NatheoHorizon/Layout/NavPanel.vue';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import MenuLink from '@/vue/Components/Front/NatheoHorizon/Menu/MenuLink.vue';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

defineOptions({ name: 'CascadeMenuList' });
defineProps<{ elements: MenuElement[] }>();

const { translate } = useFrontContext();
const { isActive } = useMenus();
const openId = ref<number | null>(null);
const openedByHover = ref(false);
// Panneau ouvert vers la gauche quand il dépasse de l'écran à droite
const toLeft = ref(false);
const panels = ref<Record<number, HTMLElement | null>>({});
const hoverClose = useHoverDelay(() => close());

// Les sous-panneaux se referment avec le panneau de l'en-tête qui les contient
const panelOpen = inject(NAV_PANEL_OPEN, null);
if (panelOpen !== null) {
  watch(panelOpen, (isOpen) => {
    if (!isOpen) {
      close();
    }
  });
}

async function open(element: MenuElement): Promise<void> {
  hoverClose.cancel();
  if (openId.value === element.id) {
    return;
  }
  toLeft.value = false;
  openId.value = element.id;
  await nextTick();
  const rect = panels.value[element.id]?.getBoundingClientRect();
  toLeft.value = rect !== undefined && rect.right > document.documentElement.clientWidth;
}

function close(): void {
  hoverClose.cancel();
  openId.value = null;
  openedByHover.value = false;
}

// Un clic sur un panneau ouvert au survol le garde ouvert au lieu de le refermer
function toggle(element: MenuElement): void {
  if (openId.value === element.id && !openedByHover.value) {
    close();
  } else {
    open(element);
  }
  openedByHover.value = false;
}

function onPointerenter(event: PointerEvent, element: MenuElement): void {
  if (!isMouse(event)) {
    return;
  }
  if (element.children.length) {
    openedByHover.value = openedByHover.value || openId.value !== element.id;
    open(element);
  } else if (openedByHover.value) {
    hoverClose.schedule();
  }
}

function onPointerleave(event: PointerEvent): void {
  if (isMouse(event) && openedByHover.value) {
    hoverClose.schedule();
  }
}

async function onKeydown(event: KeyboardEvent, element: MenuElement): Promise<void> {
  if (event.key !== 'ArrowRight' || !element.children.length) {
    return;
  }
  event.preventDefault();
  event.stopPropagation();
  await open(element);
  panels.value[element.id]?.querySelector<HTMLElement>('a[href], button')?.focus();
}

// Flèche gauche dans un sous-panneau : retour sur l'élément qui l'a ouvert
function onPanelKeydown(event: KeyboardEvent, element: MenuElement): void {
  if (event.key !== 'ArrowLeft') {
    return;
  }
  event.preventDefault();
  event.stopPropagation();
  const item = panels.value[element.id]?.closest('li');
  close();
  item?.querySelector<HTMLElement>('a[href], button')?.focus();
}

function onFocusout(event: FocusEvent, element: MenuElement): void {
  const item = (event.currentTarget as HTMLElement | null) ?? null;
  if (openId.value === element.id && !item?.contains(event.relatedTarget as Node | null)) {
    close();
  }
}
</script>

<template>
  <ul>
    <li
      v-for="element in elements"
      :key="element.id"
      class="relative"
      @pointerenter="onPointerenter($event, element)"
      @pointerleave="onPointerleave"
      @focusout="onFocusout($event, element)"
    >
      <div
        class="flex items-center rounded-nh hover:bg-surface"
        :class="{ 'bg-surface': openId === element.id }"
        @keydown="onKeydown($event, element)"
      >
        <MenuLink
          :element="element"
          class="flex min-h-11 grow items-center gap-2.5 rounded-nh px-3 text-fg no-underline"
          :class="{ 'font-semibold': element.children.length && isActive(element) }"
        />
        <button
          v-if="element.children.length"
          type="button"
          class="inline-flex size-11 shrink-0 cursor-pointer items-center justify-center rounded-nh text-fg-muted"
          :aria-label="translate.menu.submenu.replace('{label}', element.label)"
          :aria-expanded="openId === element.id"
          :aria-controls="`cascade-${element.id}`"
          @click="toggle(element)"
        >
          <HorizonIcon
            name="chevron-down"
            class="size-4"
            :class="toLeft && openId === element.id ? 'rotate-90' : '-rotate-90'"
          />
        </button>
      </div>
      <div
        v-if="element.children.length"
        v-show="openId === element.id"
        :id="`cascade-${element.id}`"
        :ref="(el) => (panels[element.id] = el as HTMLElement | null)"
        class="absolute -top-1.5 z-10"
        :class="toLeft ? 'right-full pr-2.5' : 'left-full pl-2.5'"
        @keydown="onPanelKeydown($event, element)"
      >
        <CascadeMenuList
          :elements="element.children"
          class="min-w-57 rounded-nh-lg border border-line bg-bg p-1.5 shadow-[0_18px_40px_-12px_rgb(19_10_60/0.35)]"
        />
      </div>
    </li>
  </ul>
</template>

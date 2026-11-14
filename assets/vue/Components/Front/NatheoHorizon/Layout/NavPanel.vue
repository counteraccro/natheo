<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Bouton de l'en-tête qui ouvre un panneau (sous-menu, langues) : clic, survol à la souris si demandé, Échap,
 * flèches haut / bas
 */
import { nextTick, onBeforeUnmount, onMounted, ref, useId } from 'vue';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';

const props = defineProps<{ label?: string; current?: boolean; align?: 'left' | 'right'; hover?: boolean }>();
// Délai avant fermeture : laisse le temps de passer du bouton au panneau
const HOVER_CLOSE_DELAY = 150;
const id = useId();
const open = ref(false);
const root = ref<HTMLElement | null>(null);
const button = ref<HTMLButtonElement | null>(null);
const panel = ref<HTMLElement | null>(null);
const openedByHover = ref(false);
let closeTimer: ReturnType<typeof setTimeout> | undefined;

function links(): HTMLElement[] {
  // Les liens des sous-panneaux fermés sont masqués : ils ne prennent pas le focus
  return [...(panel.value?.querySelectorAll<HTMLElement>('a[href]') ?? [])].filter(
    (link) => link.offsetParent !== null
  );
}

function close(focusButton = false): void {
  clearTimeout(closeTimer);
  open.value = false;
  openedByHover.value = false;
  if (focusButton) {
    button.value?.focus();
  }
}

// Un clic sur un panneau ouvert au survol le garde ouvert au lieu de le refermer
function onButtonClick(): void {
  open.value = openedByHover.value || !open.value;
  openedByHover.value = false;
}

function onPointerenter(event: PointerEvent): void {
  if (!props.hover || event.pointerType !== 'mouse') {
    return;
  }
  clearTimeout(closeTimer);
  if (!open.value) {
    open.value = true;
    openedByHover.value = true;
  }
}

function onPointerleave(event: PointerEvent): void {
  if (openedByHover.value && event.pointerType === 'mouse') {
    closeTimer = setTimeout(close, HOVER_CLOSE_DELAY);
  }
}

async function onButtonKeydown(event: KeyboardEvent): Promise<void> {
  if (event.key === 'ArrowDown') {
    event.preventDefault();
    open.value = true;
    await nextTick();
    links()[0]?.focus();
  }
}

function onPanelKeydown(event: KeyboardEvent): void {
  if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
    return;
  }
  event.preventDefault();
  const items = links();
  const index = items.indexOf(document.activeElement as HTMLElement);
  const step = event.key === 'ArrowDown' ? 1 : -1;
  items[(index + step + items.length) % items.length]?.focus();
}

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && open.value) {
    close(true);
  }
}

function onFocusout(event: FocusEvent): void {
  if (open.value && !root.value?.contains(event.relatedTarget as Node | null)) {
    close();
  }
}

// Un clic hors du panneau le ferme, y compris l'ouverture d'un autre panneau
function onDocumentClick(event: MouseEvent): void {
  if (open.value && !root.value?.contains(event.target as Node)) {
    close();
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick));
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick);
  clearTimeout(closeTimer);
});
</script>

<template>
  <div
    ref="root"
    class="relative"
    @keydown="onKeydown"
    @focusout="onFocusout"
    @pointerenter="onPointerenter"
    @pointerleave="onPointerleave"
  >
    <button
      ref="button"
      type="button"
      class="inline-flex min-h-11 cursor-pointer items-center gap-1.5 rounded-nh px-3 font-medium hover:bg-white/10"
      :class="props.current ? 'text-on-night' : 'text-on-night-muted'"
      :aria-expanded="open"
      :aria-controls="id"
      @click="onButtonClick"
      @keydown="onButtonKeydown"
    >
      <span v-if="props.current" class="size-2 rounded-[2px] bg-mod-third" aria-hidden="true" />
      <slot name="label">{{ props.label }}</slot>
      <HorizonIcon name="chevron-down" class="size-4 transition-transform" :class="{ 'rotate-180': open }" />
    </button>
    <div
      v-show="open"
      :id="id"
      ref="panel"
      class="absolute top-[calc(100%+8px)] z-45 rounded-nh-lg border border-line bg-bg text-fg shadow-[0_18px_40px_-12px_rgb(19_10_60/0.35)]"
      :class="props.align === 'right' ? 'right-0' : 'left-0'"
      @keydown="onPanelKeydown"
    >
      <slot />
    </div>
  </div>
</template>

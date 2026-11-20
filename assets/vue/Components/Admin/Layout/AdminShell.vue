<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ossature du back-office : sidebar et colonne principale, lien d'évitement ; sous 1024 px la sidebar devient un
 * panneau modal (Échap le ferme, le focus revient au bouton menu)
 */
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';

const navOpen = defineModel<boolean>('navOpen', { default: false });
defineSlots<{
  sidebar?: (props: { close: () => Promise<void>; mobile: boolean }) => unknown;
  header?: (props: { openNav: () => void }) => unknown;
  default?: () => unknown;
}>();

const labels = useUiLabels();
const panel = ref<HTMLElement | null>(null);
const main = ref<HTMLElement | null>(null);
const mobile = ref(false);
let media: MediaQueryList | null = null;

const onMedia = (): void => {
  mobile.value = !!media?.matches;
  if (!mobile.value) navOpen.value = false;
};

const openNav = (): void => {
  navOpen.value = true;
};

const close = async (): Promise<void> => {
  if (!navOpen.value) return;
  navOpen.value = false;
  // La colonne principale n'est plus inerte qu'après le rendu
  await nextTick();
  main.value?.querySelector<HTMLElement>('[data-nav-toggle]')?.focus();
};

const onKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape' && navOpen.value) close();
};

watch(navOpen, async (open) => {
  if (!open) return;
  await nextTick();
  panel.value?.querySelector<HTMLElement>('a, button')?.focus();
});

onMounted(() => {
  media = window.matchMedia('(max-width: 1023px)');
  media.addEventListener('change', onMedia);
  onMedia();
  window.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
  media?.removeEventListener('change', onMedia);
  window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
  <div class="grid min-h-screen grid-cols-[272px_minmax(0,1fr)] bg-canvas text-fg max-lg:grid-cols-[minmax(0,1fr)]">
    <a
      href="#main-content"
      class="fixed -top-12 left-3 z-[60] rounded-sm bg-primary px-3.5 py-2.5 font-semibold text-on-primary no-underline focus:top-3"
    >
      {{ labels.shell.skipToContent }}
    </a>
    <div
      ref="panel"
      class="sticky top-0 z-40 h-screen transition-[transform,visibility] duration-200 ease-out-soft max-lg:fixed max-lg:inset-y-0 max-lg:left-0 max-lg:w-[min(300px,86vw)]"
      :class="!navOpen && 'max-lg:invisible max-lg:-translate-x-full'"
      :role="mobile && navOpen ? 'dialog' : undefined"
      :aria-modal="mobile && navOpen ? 'true' : undefined"
      :aria-label="mobile && navOpen ? labels.sidebar.nav : undefined"
    >
      <slot name="sidebar" :close="close" :mobile="mobile" />
    </div>
    <div
      v-if="mobile && navOpen"
      class="fixed inset-0 z-[35] bg-[rgb(17_17_20/.5)] backdrop-blur-[2px]"
      aria-hidden="true"
      @click="close"
    />
    <div ref="main" class="flex min-w-0 flex-col" :inert="(mobile && navOpen) || undefined">
      <slot name="header" :open-nav="openNav" />
      <main
        id="main-content"
        tabindex="-1"
        class="flex w-full flex-col gap-5 px-7 pt-6 pb-14 focus:outline-none max-md:px-3.5 max-md:pt-4 max-md:pb-30"
      >
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Volet ouvert depuis la barre latérale (menu, langue, colonnes d'un méga menu) : Échap ou le bouton le ferment
 */
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';

defineProps<{ id: string; title: string; wide: boolean }>();
const emit = defineEmits<{ close: [] }>();
const { translate } = useFrontContext();
const closeButton = ref<HTMLButtonElement | null>(null);

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    emit('close');
  }
}

onMounted(() => {
  closeButton.value?.focus();
  document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
  <div
    class="fixed inset-x-0 top-0 bottom-16 z-24 bg-[rgb(13_17_20/0.45)] md:bottom-0 md:left-19 xl:left-58"
    aria-hidden="true"
    @click="emit('close')"
  />
  <section
    :id="id"
    role="dialog"
    :aria-label="title"
    class="fixed top-0 bottom-16 left-0 z-25 flex w-screen flex-col gap-8 overflow-y-auto border-l border-rail-line bg-rail px-8 pt-8 pb-6 text-on-rail shadow-[30px_0_60px_-30px_rgb(0_0_0/0.5)] [--focus:var(--bolt)] md:bottom-0 md:left-19 xl:left-58"
    :class="
      wide
        ? 'md:w-[min(760px,calc(100vw-76px))] xl:w-[min(760px,calc(100vw-232px))]'
        : 'md:w-[min(420px,calc(100vw-76px))] xl:w-[min(420px,calc(100vw-232px))]'
    "
  >
    <div class="flex items-center justify-between gap-4">
      <h2 class="text-[1.75rem]">{{ title }}</h2>
      <button
        ref="closeButton"
        type="button"
        class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-sf text-on-rail-muted hover:text-on-rail"
        @click="emit('close')"
      >
        <span class="sr-only">{{ translate.header.closeMenu }}</span>
        <UiIcon name="close" class="size-5.5" />
      </button>
    </div>
    <slot />
  </section>
</template>

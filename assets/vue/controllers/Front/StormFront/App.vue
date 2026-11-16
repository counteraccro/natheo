<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant racine du thème StormFront, un lecteur en trois volets : barre latérale, fil de la catégorie et page
 * ouverte, chargée sans rechargement depuis le fil
 */
import { computed, nextTick, ref, watch } from 'vue';
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { usePage } from '@/vue/Composables/Front/usePage';
import { useReadingProgress } from '@/vue/Composables/Front/useReadingProgress';
import ConsentBanner from '@/vue/Components/Front/StormFront/Layout/ConsentBanner.vue';
import ErrorState from '@/vue/Components/Front/StormFront/Layout/ErrorState.vue';
import PageSkeleton from '@/vue/Components/Front/StormFront/Layout/PageSkeleton.vue';
import Rail from '@/vue/Components/Front/StormFront/Layout/Rail.vue';
import ReaderFooter from '@/vue/Components/Front/StormFront/Layout/ReaderFooter.vue';
import PageView from '@/vue/Components/Front/StormFront/Page/PageView.vue';
import ThreadPane from '@/vue/Components/Front/StormFront/Thread/ThreadPane.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
const { translate } = createFrontContext(props);
const { data: page, loading, error } = usePage();

const THREAD_ID = 'sf-thread';
const reader = ref<HTMLElement | null>(null);
const progress = useReadingProgress(undefined, reader);
const threadOpen = ref(false);
const announce = ref('');
// Une page de la catégorie « page » (accueil, mentions…) n'a pas de fil
const hasThread = computed(() => page.value !== null && page.value.category !== 'page');

// Page ouverte sans rechargement : lecture reprise en haut, titre annoncé et mis au point
watch(
  () => page.value?.id,
  async (id, previous) => {
    if (id === undefined || previous === undefined) {
      return;
    }
    threadOpen.value = false;
    announce.value = translate.thread.opened.replace('{title}', page.value?.title ?? '');
    await nextTick();
    reader.value?.scrollTo(0, 0);
    window.scrollTo(0, 0);
    document.getElementById('page-title')?.focus({ preventScroll: true });
  }
);
</script>

<template>
  <a
    href="#contenu"
    class="absolute -top-16 left-4 z-100 rounded-sf bg-page px-4 py-3 font-semibold text-fg focus:top-4"
  >
    {{ translate.header.skipLink }}
  </a>
  <ConsentBanner />
  <div
    class="md:grid md:h-dvh md:overflow-hidden"
    :class="
      hasThread
        ? 'md:grid-cols-[76px_300px_minmax(0,1fr)] xl:grid-cols-[232px_340px_minmax(0,1fr)]'
        : 'md:grid-cols-[76px_minmax(0,1fr)] xl:grid-cols-[232px_minmax(0,1fr)]'
    "
  >
    <Rail
      :page="page"
      :has-thread="hasThread"
      :thread-open="threadOpen"
      :thread-id="THREAD_ID"
      @toggle-thread="threadOpen = !threadOpen"
    />
    <ThreadPane
      v-if="page && hasThread"
      :id="THREAD_ID"
      :page="page"
      :progress="progress"
      :open="threadOpen"
      @chosen="threadOpen = false"
    />
    <main
      id="contenu"
      ref="reader"
      tabindex="-1"
      class="relative min-w-0 outline-none md:h-dvh md:overflow-y-auto"
      :aria-busy="loading"
    >
      <div v-if="page && !hasThread" class="sticky top-0 z-5 h-1.25 bg-line" aria-hidden="true">
        <span class="absolute inset-0 origin-left bg-primary" :style="{ transform: `scaleX(${progress})` }" />
      </div>
      <PageSkeleton v-if="loading && !page" />
      <ErrorState v-else-if="error" :error="error" />
      <PageView v-else-if="page" :key="page.id" :page="page" :class="{ 'opacity-60': loading }" />
      <ReaderFooter :page="page" />
    </main>
  </div>
  <p class="sr-only" aria-live="polite">{{ announce }}</p>
</template>

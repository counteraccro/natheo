<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fil : pages de la catégorie de la page ouverte, la dernière mise à jour en haut ; l'article ouvert porte la barre
 * de lecture
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useCategoryPages } from '@/vue/Composables/Front/useCategoryPages';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import { usePageLink } from '@/vue/Composables/Front/usePageLink';
import TagList from '@/vue/Components/Front/StormFront/Thread/TagList.vue';
import type { Page, PageSummary } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ id: string; page: Page; progress: number; open: boolean }>();
const emit = defineEmits<{ chosen: [] }>();
const context = useFrontContext();
const { translate } = context;
const format = useFormat();
const category = computed(() => props.page.category);
const { items, total, hasMore, loading, error, loadMore, pageUrl } = useCategoryPages(category);
const titleId = `${props.id}-title`;
const openPage = usePageLink();

function choose(event: MouseEvent, summary: PageSummary): void {
  if (openPage(event, summary)) {
    emit('chosen');
  }
}
</script>

<template>
  <aside
    :id="id"
    :aria-labelledby="titleId"
    :aria-busy="loading"
    class="fixed inset-x-0 top-0 bottom-16 z-20 overflow-y-auto border-r border-line bg-thread md:static md:block md:h-dvh"
    :class="{ hidden: !open }"
  >
    <div class="sticky top-0 z-2 border-b border-line bg-thread px-6 pt-6 pb-4">
      <h2 :id="titleId" class="text-[1.9rem]">{{ translate.categories[page.category] ?? page.category }}</h2>
      <p v-if="total" class="mt-1 text-[0.92rem] text-fg-muted">{{ format.plural(total, translate.main.listing.pages) }}</p>
      <p class="text-[0.92rem] text-fg-muted">{{ translate.thread.order }}</p>
    </div>

    <ol>
      <li
        v-for="summary in items"
        :key="summary.id"
        class="relative border-b border-line px-6 pt-4.5 pb-5 focus-within:outline-3 focus-within:-outline-offset-3 focus-within:outline-(--focus)"
        :class="{ 'bg-page shadow-[inset_4px_0_0_var(--primary)]': summary.id === page.id }"
      >
        <time v-if="summary.updatedAt" :datetime="summary.updatedAt" class="mb-1 block font-mono text-[0.82rem] text-fg-muted">
          {{ format.date(summary.updatedAt) }}
        </time>
        <h3 class="text-[1.3rem] leading-[1.18]">
          <a
            :href="pageUrl(summary)"
            :aria-current="summary.id === page.id ? 'page' : undefined"
            class="text-fg no-underline after:absolute after:inset-0 hover:underline focus-visible:outline-none aria-[current=page]:text-primary"
            @click="choose($event, summary)"
          >
            {{ summary.title }}
          </a>
        </h3>
        <p v-if="summary.author" class="mt-1.5 text-[0.88rem] text-fg-muted">{{ summary.author.name }}</p>
        <TagList :tags="summary.tags" class="mt-2" />
        <div
          v-if="summary.id === page.id"
          class="relative mt-3.5 h-1.25 overflow-hidden rounded-[2px] bg-line"
          aria-hidden="true"
        >
          <span class="absolute inset-0 origin-left bg-primary" :style="{ transform: `scaleX(${progress})` }" />
        </div>
      </li>
    </ol>

    <div v-if="loading && !items.length" aria-hidden="true">
      <div v-for="line in 3" :key="line" class="border-b border-line px-6 py-5">
        <div class="mb-2.5 h-3 w-1/3 rounded-sf bg-line motion-safe:animate-breathe" />
        <div class="h-5 w-5/6 rounded-sf bg-line motion-safe:animate-breathe" />
      </div>
    </div>
    <p v-if="error" role="alert" class="px-6 py-5 text-fg-muted">{{ context.errorLabel(error) }}</p>

    <div v-if="items.length" class="px-6 pt-5 pb-8">
      <button
        v-if="hasMore"
        type="button"
        class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-sf font-semibold text-fg shadow-[inset_0_0_0_1.5px_currentColor] disabled:opacity-60"
        :disabled="loading"
        @click="loadMore"
      >
        {{ translate.thread.more }}
      </button>
      <p v-else class="text-center text-[0.92rem] text-fg-muted">{{ translate.thread.end }}</p>
    </div>
  </aside>
</template>

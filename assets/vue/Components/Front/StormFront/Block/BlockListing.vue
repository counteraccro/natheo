<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc liste de pages : entrées datées, vignette à droite en pleine largeur, lignes simples en moitié ou en tiers
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import { useListing } from '@/vue/Composables/Front/useListing';
import { usePageLink } from '@/vue/Composables/Front/usePageLink';
import PaginationNav from '@/vue/Components/Front/StormFront/Page/PaginationNav.vue';
import TagList from '@/vue/Components/Front/StormFront/Thread/TagList.vue';
import type { BlockSize } from '@/vue/Composables/Front/usePageBlocks';
import type { ListingContent } from '@/ts/Front/Api/PageContent.type';

const props = defineProps<{ content: ListingContent; size: BlockSize }>();
const { translate } = useFrontContext();
const openPage = usePageLink();
const { data: pages, loading, page, goTo, pageUrl } = useListing(props.content);
const full = computed(() => props.size === 'full');
const format = useFormat();
</script>

<template>
  <div :aria-busy="loading">
    <div class="flex items-baseline justify-between gap-4">
      <h2 class="mb-4 text-[clamp(1.7rem,2.6vw,2.15rem)]">{{ content.title }}</h2>
      <p v-if="pages" class="shrink-0 text-[0.92rem] text-fg-muted">
        {{ format.plural(pages.total, translate.main.listing.pages) }}
      </p>
    </div>
    <template v-if="pages">
      <p v-if="pages.total === 0" class="text-fg-muted">{{ translate.main.listing.empty }}</p>
      <ol>
        <li
          v-for="summary in pages.members"
          :key="summary.id"
          class="relative grid gap-x-8 border-t border-line last:border-b focus-within:outline-3 focus-within:outline-offset-4 focus-within:outline-(--focus)"
          :class="full ? 'py-6 md:grid-cols-[minmax(0,1fr)_200px]' : 'py-4'"
        >
          <div class="min-w-0">
            <time v-if="summary.createdAt" :datetime="summary.createdAt" class="font-mono text-[0.82rem] text-fg-muted">
              {{ format.date(summary.createdAt) }}
            </time>
            <h3 class="mt-1" :class="full ? 'text-[1.7rem]' : 'text-[1.2rem]'">
              <a
                :href="pageUrl(summary)"
                class="text-fg no-underline after:absolute after:inset-0 hover:text-primary focus-visible:outline-none"
                @click="openPage($event, summary)"
              >
                {{ summary.title }}
              </a>
            </h3>
            <p v-if="summary.author" class="mt-1.5 text-[0.92rem] text-fg-muted">
              {{ translate.main.listing.by }} {{ summary.author.name }}
            </p>
            <TagList v-if="full" :tags="summary.tags" class="mt-2.5" />
          </div>
          <img
            v-if="full && summary.headerImage"
            :src="summary.headerImage"
            alt=""
            loading="lazy"
            class="hidden aspect-3/2 w-full rounded-sf object-cover md:block"
          />
        </li>
      </ol>
      <PaginationNav
        :page="page"
        :last-page="pages.lastPage"
        :translate="translate.main.pagination"
        :compact="!full"
        @go="goTo"
      />
    </template>
  </div>
</template>

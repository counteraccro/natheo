<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc liste de pages : cartes en pleine largeur, lignes compactes en moitié ou en tiers
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import { useListing } from '@/vue/Composables/Front/useListing';
import PaginationNav from '@/vue/Components/Front/NatheoHorizon/Page/PaginationNav.vue';
import type { BlockSize } from '@/vue/Composables/Front/usePageBlocks';
import type { ListingContent } from '@/ts/Front/Api/PageContent.type';

const props = defineProps<{ content: ListingContent; size: BlockSize }>();
const { translate } = useFrontContext();
const { data: pages, loading, page, goTo, pageUrl } = useListing(props.content);
const compact = computed(() => props.size !== 'full');

const format = useFormat();
</script>

<template>
  <div class="@container" :aria-busy="loading">
    <div class="flex items-baseline justify-between gap-4">
      <h2 class="mb-4 text-[clamp(1.5rem,2.4vw,2rem)]">{{ content.title }}</h2>
      <p v-if="pages" class="shrink-0 text-[0.95rem] text-fg-muted">
        {{ format.plural(pages.total, translate.main.listing.pages) }}
      </p>
    </div>
    <template v-if="pages">
      <p v-if="pages.total === 0" class="text-fg-muted">{{ translate.main.listing.empty }}</p>
      <ul :class="compact ? 'grid' : 'mt-2 grid gap-x-8 gap-y-10 @lg:grid-cols-2 @3xl:grid-cols-3'">
        <li
          v-for="summary in pages.members"
          :key="summary.id"
          class="relative rounded-nh-lg focus-within:outline-3 focus-within:outline-offset-4 focus-within:outline-(--focus)"
          :class="
            compact
              ? 'grid grid-cols-[88px_1fr] content-start gap-x-4 gap-y-0.5 border-b border-line py-4'
              : 'flex flex-col gap-2'
          "
        >
          <img
            v-if="summary.headerImage"
            :src="summary.headerImage"
            alt=""
            class="w-full rounded-nh-lg bg-surface object-cover"
            :class="compact ? 'row-span-2 aspect-square' : 'aspect-3/2'"
          />
          <span
            v-else
            class="w-full rounded-nh-lg bg-surface"
            :class="compact ? 'row-span-2 aspect-square' : 'aspect-3/2'"
            aria-hidden="true"
          />
          <h3 :class="compact ? 'text-[1.05rem] leading-tight' : 'text-[1.2rem] leading-tight'">
            <a
              :href="pageUrl(summary)"
              class="text-fg no-underline after:absolute after:inset-0 hover:underline focus-visible:outline-none"
            >
              {{ summary.title }}
            </a>
          </h3>
          <p class="text-[0.92rem] text-fg-muted">
            <template v-if="summary.author">{{ translate.main.listing.by }} {{ summary.author.name }}, </template>
            {{ format.date(summary.createdAt) }}
          </p>
        </li>
      </ul>
      <PaginationNav
        :page="page"
        :last-page="pages.lastPage"
        :translate="translate.main.pagination"
        :compact="compact"
        @go="goTo"
      />
    </template>
  </div>
</template>

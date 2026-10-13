<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Contenu de type listing
 */
import { defineComponent, type PropType } from 'vue';
import Pagination from './Pagination.vue';
import type { FrontApi } from '@/utils/Front/FrontApi';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontApiError, FrontMainTranslate, FrontPageContent } from '@/ts/Front/NatheoHorizon/Structure.type';
import type { ContentListingResponse, ListingPage } from '@/ts/Front/NatheoHorizon/ContentListing.type';

export default defineComponent({
  name: 'ContentListing',
  components: { Pagination },
  // ContentStructure passe les mêmes props à tous les types de contenu
  inheritAttrs: false,
  props: {
    data: {
      type: Object as PropType<FrontPageContent>,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    frontApi: {
      type: Object as PropType<FrontApi>,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontMainTranslate>,
      required: true,
    },
  },
  emits: {
    'api-failure': (_error: FrontApiError) => true,
  },
  data() {
    return {
      isLoad: false,
      limit: 20,
      page: 1,
      pages: [] as ListingPage[],
      nbElements: 0,
      title: '',
    };
  },
  computed: {
    nbPages(): number {
      return Math.ceil(this.nbElements / this.limit);
    },
  },
  created() {
    this.loadContent();
  },
  methods: {
    loadContent(): void {
      this.isLoad = false;
      this.frontApi
        .getContentPage<ContentListingResponse>({
          id: this.data.id,
          locale: this.locale,
          limit: this.limit,
          page: this.page,
        })
        .then((response) => {
          this.pages = response.content.pages;
          this.title = response.title;
          this.nbElements = response.content.rows;
          this.isLoad = true;
        })
        .catch((error: FrontApiError) => this.$emit('api-failure', error));
    },

    changePage(page: number): void {
      this.page = page;
      this.loadContent();
      (this.$refs.top as HTMLElement | undefined)?.scrollIntoView({ behavior: 'smooth' });
    },
  },
});
</script>

<template>
  <h2 ref="top" class="text-slate-900 text-2xl font-semibold mb-3">{{ title }}</h2>
  <div v-if="isLoad && pages.length > 0">
    <ul class="mx-auto divide-y divide-gray-200">
      <li v-for="item in pages" :key="item.slug" class="py-2">
        <a
          :href="utilsFront.getUrl(item)"
          class="flex gap-4 hover:bg-theme-4-750/15 hover:dark:bg-gray-600 rounded-xl p-2 transition"
        >
          <img
            v-if="item.img"
            :src="item.img"
            :alt="item.title"
            class="h-28 w-40 flex-none rounded-xl object-cover"
            loading="lazy"
          />
          <div
            v-else
            class="h-28 w-40 flex-none rounded-xl bg-gray-200 flex flex-col items-center justify-center text-gray-400 text-sm font-medium"
          >
            <div class="h-10 w-16 bg-gray-300 rounded-md mb-2"></div>
            <span>{{ translate.listing.noImage }}</span>
          </div>
          <div class="min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
              {{ item.title }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ translate.listing.by }} <span class="font-medium text-gray-700">{{ item.author }}</span> •
              {{ utilsFront.formatDate(item.created * 1000) }}
            </p>
          </div>
        </a>
      </li>
    </ul>

    <Pagination :page="page" :nb-pages="nbPages" :translate="translate.pagination" @change="changePage" />
    <div class="flex items-center justify-center mt-2 text-gray-400 text-sm">
      {{ page }} {{ translate.pagination.of }} {{ nbPages }} - {{ nbElements }} {{ translate.listing.nbPages }}
    </div>
  </div>

  <div v-else-if="isLoad">
    <div class="p-6">
      <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 p-8 text-center"
      >
        <p class="text-gray-500 text-lg font-medium">{{ translate.listing.empty }}</p>
      </div>
    </div>
  </div>

  <div v-else class="mx-auto w-full rounded-md p-4">
    <ul class="divide-y divide-gray-200">
      <li class="flex items-center gap-4 pb-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

      <li class="flex items-center gap-4 py-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

      <li class="flex items-center gap-4 py-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

      <li class="flex items-center gap-4 py-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>
    </ul>
  </div>
</template>

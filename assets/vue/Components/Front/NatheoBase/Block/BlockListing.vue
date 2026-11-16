<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc listing du thème Natheo-Base : pages d'une catégorie, paginées
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useListing } from '@/vue/Composables/Front/useListing';
import PaginationNav from '@/vue/Components/Front/NatheoBase/PaginationNav.vue';
import type { ListingContent } from '@/ts/Front/Api/PageContent.type';

const props = defineProps<{ content: ListingContent }>();
const { translate } = useFrontContext();
const { data: pages, loading, page, goTo, pageUrl } = useListing(props.content);
</script>

<template>
  <div class="space-y-4" :aria-busy="loading">
    <h2 class="text-2xl font-semibold">{{ content.title }}</h2>
    <template v-if="pages">
      <p v-if="pages.total === 0">{{ translate.main.listing.empty }}</p>
      <ul class="space-y-3">
        <li v-for="summary in pages.members" :key="summary.id">
          <a :href="pageUrl(summary)" class="font-medium underline">{{ summary.title }}</a>
          <p v-if="summary.author" class="text-sm text-gray-500">
            {{ translate.main.listing.by }} {{ summary.author.name }}
          </p>
        </li>
      </ul>
      <PaginationNav :page="page" :last-page="pages.lastPage" :translate="translate.main.pagination" @go="goTo" />
    </template>
  </div>
</template>

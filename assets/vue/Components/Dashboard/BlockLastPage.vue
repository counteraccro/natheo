<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Composant card derniers commentaires
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import type {
  BlockLastPageUrls,
  BlockLastPageTranslate,
  LoadBlockDashboardResponse,
  Page,
} from '@/ts/Dashboard/BlockLastPage.type';

export default defineComponent({
  name: 'BlockLastPage',
  components: { AlertDanger, SkeletonTable },
  props: {
    urls: {
      type: Object as PropType<BlockLastPageUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<BlockLastPageTranslate>,
      required: true,
    },
  },
  emits: ['reload-grid'],
  data() {
    return {
      loading: false as boolean,
      errorMessage: null as string | null,
      result: [] as Page[] | null,
    };
  },
  mounted() {
    this.load();
  },
  methods: {
    /**
     * Chargement du module
     */
    load(): void {
      this.loading = true;
      axios
        .get<LoadBlockDashboardResponse>(this.urls.load_block_dashboard)
        .then((response) => {
          if (!response.data.success) {
            this.errorMessage = response.data.error;
          } else {
            this.result = response.data.body;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.reload();
        });
    },

    reload(): void {
      this.$emit('reload-grid');
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-width="2"
              d="M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 18h8l-2-4-1.5 2-2-4L8 18Zm7-8.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0Z"
            />
          </svg>
          {{ translate.title }}
        </div>
      </div>
      <a :href="urls.url_pages" class="text-sm font-medium hover:underline flex items-center gap-1 text-(--primary)">
        {{ translate.link_page }}
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3" />
        </svg>
      </a>
    </div>
    <div class="overflow-x-auto m-4" v-if="!loading">
      <AlertDanger v-if="errorMessage !== null" :text="errorMessage" />
      <table class="w-full" v-if="result !== null">
        <thead class="bg-(--bg-main)">
          <tr>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-(--text-secondary)"
            >
              {{ translate.table_id }}
            </th>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-(--text-secondary)"
            >
              {{ translate.table_title }}
            </th>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-(--text-secondary)"
            >
              {{ translate.table_status }}
            </th>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-(--text-secondary)"
            >
              {{ translate.table_date }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-(--border-color)">
          <tr v-for="page in result" :key="page.id" class="hover:bg-gray-50 transition bg-(--bg-card)">
            <td class="px-4 sm:px-6 py-4 text-sm font-medium">#{{ page.id }}</td>
            <td class="px-4 sm:px-6 py-4 text-sm">{{ page.title }}</td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap"><span class="badge" v-html="page.status"></span></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-(--text-secondary)">{{ page.date }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <SkeletonTable :rows="5" :columns="3" />
    </div>
  </div>
</template>

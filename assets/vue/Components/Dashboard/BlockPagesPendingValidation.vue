<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card des pages en attente de validation
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import type {
  BlockPagesPendingValidationUrls,
  BlockPagesPendingValidationTranslate,
  LoadBlockDashboardResponse,
  PendingPage,
} from '@/ts/Dashboard/BlockPagesPendingValidation.type';
import { toastRequestError } from '@/vue/Composables/Global/toast';

export default defineComponent({
  name: 'BlockPagesPendingValidation',
  components: { AlertDanger, SkeletonTable },
  props: {
    urls: {
      type: Object as PropType<BlockPagesPendingValidationUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<BlockPagesPendingValidationTranslate>,
      required: true,
    },
  },
  data() {
    return {
      loading: false as boolean,
      errorMessage: null as string | null,
      result: null as PendingPage[] | null,
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
          toastRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
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
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>
          {{ translate.title }}
        </div>
      </div>
      <a :href="urls.url_pages" class="text-sm font-medium hover:underline flex items-center gap-1 text-primary">
        {{ translate.link_page }}
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3" />
        </svg>
      </a>
    </div>
    <div class="overflow-x-auto m-4" v-if="!loading">
      <AlertDanger v-if="errorMessage !== null" :text="errorMessage" />
      <p v-else-if="result !== null && result.length === 0" class="text-sm text-fg-muted">
        {{ translate.empty }}
      </p>
      <table class="w-full" v-else-if="result !== null">
        <thead class="bg-surface">
          <tr>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-fg-muted">
              {{ translate.column_id }}
            </th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-fg-muted">
              {{ translate.column_title }}
            </th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-fg-muted">
              {{ translate.column_author }}
            </th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-fg-muted">
              {{ translate.column_date }}
            </th>
            <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-fg-muted">
              {{ translate.column_action }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          <tr v-for="page in result" :key="page.id" class="hover:bg-surface-hover transition bg-card">
            <td class="px-4 sm:px-6 py-4 text-sm font-medium">#{{ page.id }}</td>
            <td class="px-4 sm:px-6 py-4 text-sm">{{ page.title }}</td>
            <td class="px-4 sm:px-6 py-4 text-sm">{{ page.author }}</td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-fg-muted">{{ page.date }}</td>
            <td class="px-4 sm:px-6 py-4 text-right">
              <a :href="page.url" class="btn btn-ghost-primary btn-xs btn-icon" :title="translate.link_edit">
                <svg class="icon-sm" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                  <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"
                  />
                </svg>
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <SkeletonTable :rows="5" :columns="5" />
    </div>
  </div>
</template>

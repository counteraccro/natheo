<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card derniers commentaires
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import type {
  BlockLastCommentUrls,
  BlockLastCommentTranslate,
  LoadBlockDashboardResponse,
  Comment,
} from '@/ts/Dashboard/BlockLastComment.type';

export default defineComponent({
  name: 'BlockLastComment',
  components: { AlertDanger, SkeletonTable },
  props: {
    urls: {
      type: Object as PropType<BlockLastCommentUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<BlockLastCommentTranslate>,
      required: true,
    },
  },
  emits: ['reload-grid'],
  data() {
    return {
      loading: false as boolean,
      errorMessage: null as string | null,
      result: null as Comment[] | null,
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
          if (response.data.success === false) {
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
            <path stroke-width="2" d="M9 17h6l3 3v-3h2V9h-2M4 4h11v8H9l-3 3v-3H4V4Z" />
          </svg>
          {{ translate.title }}
        </div>
      </div>
      <a :href="urls.url_comments" class="text-sm font-medium hover:underline flex items-center gap-1 text-(--primary)">
        {{ translate.link_comment }}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3" />
        </svg>
      </a>
    </div>
    <AlertDanger v-if="errorMessage !== null" :text="errorMessage" />

    <div class="overflow-x-auto" v-if="result !== null">
      <table class="w-full">
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
              {{ translate.table_author }}
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
          <tr v-for="comment in result" :key="comment.id" class="hover:bg-gray-50 transition bg-(--bg-card)">
            <td class="px-4 sm:px-6 py-4 text-sm font-medium">#{{ comment.id }}</td>
            <td class="px-4 sm:px-6 py-4 text-sm">{{ comment.author }}</td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap"><span class="badge" v-html="comment.status"></span></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-(--text-secondary)">{{ comment.date }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <SkeletonTable :rows="5" :columns="3" />
    </div>
  </div>
</template>

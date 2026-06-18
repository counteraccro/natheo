<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card derniers commentaires
 */
import axios from 'axios';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';

export default {
  name: 'BlockLastComment',
  components: { AlertDanger, SkeletonTable },
  emit: [],
  props: {
    urls: Object,
    translate: Object,
  },
  emits: ['reload-grid'],
  data() {
    return {
      loading: false,
      errorMessage: null,
      result: null,
    };
  },
  mounted() {
    this.load();
  },
  methods: {
    /**
     * Chargement du module
     */
    load() {
      this.loading = true;
      axios
        .get(this.urls.load_block_dashboard)
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

    reload() {
      this.$emit('reload-grid');
    },
  },
};
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-width="2"
              d="M7 9h5m3 0h2M7 12h2m3 0h5M5 5h14a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1h-6.616a1 1 0 0 0-.67.257l-2.88 2.592A.5.5 0 0 1 8 18.477V17a1 1 0 0 0-1-1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"
            />
          </svg>
          {{ this.translate.title }}
        </div>
      </div>
      <a
        :href="this.urls.url_comments"
        class="text-sm font-medium hover:underline flex items-center gap-1"
        style="color: var(--primary)"
      >
        {{ this.translate.link_comment }}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="2" d="M13 5h6m0 0v6m0-6L10 14m-4 5h8a2 2 0 0 0 2-2v-3" />
        </svg>
      </a>
    </div>
    <AlertDanger v-if="this.errorMessage !== null" :text="this.errorMessage" />

    <div class="overflow-x-auto" v-if="this.result !== null">
      <table class="w-full">
        <thead style="background-color: var(--bg-main)">
          <tr>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              style="color: var(--text-secondary)"
            >
              {{ this.translate.table_id }}
            </th>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              style="color: var(--text-secondary)"
            >
              {{ this.translate.table_author }}
            </th>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              style="color: var(--text-secondary)"
            >
              {{ this.translate.table_status }}
            </th>
            <th
              class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              style="color: var(--text-secondary)"
            >
              {{ this.translate.table_date }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--border-color)]">
          <tr
            v-for="comment in this.result"
            class="hover:bg-gray-50 transition"
            style="background-color: var(--bg-card)"
          >
            <td class="px-4 sm:px-6 py-4 text-sm font-medium">#{{ comment.id }}</td>
            <td class="px-4 sm:px-6 py-4 text-sm">{{ comment.author }}</td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap"><span class="badge" v-html="comment.status"></span></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary)">{{ comment.date }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <SkeletonTable :rows="5" :columns="3" />
    </div>
  </div>
</template>

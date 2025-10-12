<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card derniers commentaires
 */
import axios from 'axios';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';

export default {
  name: 'BlockLastPage',
  components: { SkeletonTable },
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
  <div class="card rounded-lg overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b flex items-center justify-between" style="border-color: var(--border-color)">
      <h3 class="text-lg font-semibold">Dernières pages</h3>
      <button class="text-sm font-medium hover:underline" style="color: var(--primary)">Voir tout</button>
    </div>

    <div class="overflow-x-auto" v-if="!this.loading">
      <div
        v-if="this.errorMessage !== null"
        class="ms-4 me-4 mt-4 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
        role="alert"
      >
        {{ this.errorMessage }}
      </div>
    </div>
    <div v-else>
      <SkeletonTable :rows="5" :columns="3" />
    </div>
  </div>
</template>

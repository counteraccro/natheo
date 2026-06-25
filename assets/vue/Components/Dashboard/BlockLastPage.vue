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
  name: 'BlockLastPage',
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
          {{ 'Dernières pages' }}
        </div>
      </div>
      <a
        :href="this.urls.url_comments"
        class="text-sm font-medium hover:underline flex items-center gap-1"
        style="color: var(--primary)"
      >
        {{ 'Voir tout' }}
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="2" d="M13 5h6m0 0v6m0-6L10 14m-4 5h8a2 2 0 0 0 2-2v-3" />
        </svg>
      </a>
    </div>
    <div class="overflow-x-auto m-4" v-if="!this.loading">
      <AlertDanger v-if="this.errorMessage !== null" :text="this.errorMessage" />
    </div>
    <div v-else>
      <SkeletonTable :rows="5" :columns="3" />
    </div>
  </div>
</template>

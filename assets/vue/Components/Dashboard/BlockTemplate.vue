<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card derniers commentaires
 */
import axios from 'axios';

export default {
  name: 'BlockLastComment',
  components: {},
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
  <div class="card">
    <h5 class="card-header"><i class="bi bi-chat-left-text"></i> {{ this.translate.title }}</h5>

    <div class="card-body" v-if="this.loading">
      <div class="spinner-border spinner-border-sm text-secondary" role="status">
        <span class="visually-hidden">{{ this.translate.loading }}</span>
      </div>
      {{ this.translate.loading }}
    </div>

    <div class="card-body" v-else>
      <div v-if="this.errorMessage !== null"><i class="bi bi-exclamation-circle"></i> {{ this.errorMessage }}</div>
    </div>
  </div>
</template>

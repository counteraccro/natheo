<script>/**
 * Permet de modérer 1 commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";

export default {
  name: 'CommentEdit',
  props: {
    urls: Object,
    translate: Object,
    id: Number
  },
  emits: [],
  mounted() {
    this.load();
  },
  data() {
    return {
      loading: false,
      comment: [],
    }
  },
  methods: {
    load() {
      this.loading = true;
      axios.get(this.urls.load_comment + '/' + this.id).then((response) => {

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    }

  }
}
</script>

<template>

  <div id="block-faq" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="comment">
      {{ this.translate.loading }}
    </div>
  </div>
</template>
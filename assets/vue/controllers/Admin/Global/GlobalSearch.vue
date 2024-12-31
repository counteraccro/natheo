<script>/**
 * Permet de faire une recherche globale dans le CMS
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";


export default {
  name: 'GlobalSearch',
  props: {
    search: String,
    translate: Object,
    urls: Object
  },
  emits: [],
  data() {
    return {
      loading : {
        page : false
      }
    }
  },
  mounted() {
    this.searchPage();
  },
  methods: {
    searchPage()
    {
      this.loading.page = true;
      axios.get(this.urls.searchPage + '/' + this.search, {})
          .then((response) => {

          }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading.page = false;
      });
    }
  }

}
</script>

<template>
  <h2>{{ this.translate.subTitlePage }}</h2>
  <div :class="this.loading.page === true ? 'block-grid' : ''">
    <div v-if="this.loading.page" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loadingPage }}</span>
      </div>
    </div>

  </div>
 La recherche {{ this.search }}
</template>
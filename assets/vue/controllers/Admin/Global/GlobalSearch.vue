<script>/**
 * Permet de faire une recherche globale dans le CMS
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import TabResultSearch from "../../../Components/Global/Search/TabResultSearch.vue";


export default {
  name: 'GlobalSearch',
  components: {TabResultSearch},
  props: {
    search: String,
    translate: Object,
    urls: Object
  },
  emits: [],
  data() {
    return {
      loading: {
        page: false
      },
      results: {
        page: null
      },
      paginate: {
        page: null
      }
    }
  },
  mounted() {
    this.globalSearch('page', this.search, 1, 1);
  },
  methods: {

    changePage(entity, page, limit)
    {
      this.globalSearch(entity, this.search, page, limit)
    },

    globalSearch(entity, search, page, limit) {
      this.loading[entity] = true;
      axios.get(this.urls.searchPage + '/' + entity + '/' + search + '/' + page + '/' + limit, {})
          .then((response) => {
            this.results[entity] = response.data.result;
            this.paginate[entity] = response.data.paginate;
          }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading[entity] = false;
      });
    }
  }

}
</script>

<template>
  <h2>{{ this.translate.subTitlePage }}
    <a :href="this.urls.listingPage" class="float-end btn btn-secondary"><i class="bi bi-file-earmark-text-fill"></i> </a></h2>
  <div :class="this.loading.page === true ? 'block-grid' : ''">
    <div v-if="this.loading.page" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loadingPage }}</span>
      </div>
    </div>
    <div v-if="this.results.page === null">
      aaaa

    </div>
    <div v-else>
      <tab-result-search
          :result="this.results.page"
          :translate="this.translate"
          :paginate="this.paginate.page"
          :entity="'page'"
          @change-page-event="this.changePage"
      >
      </tab-result-search>
    </div>


  </div>
  La recherche {{ this.search }}
</template>
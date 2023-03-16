<script>
import Grid from '../../Components/Grid/Grid.vue'
import GridPaginate from "../../Components/Grid/GridPaginate.vue";
import axios from "axios";

export default {
  name: "GenericGri",
  components: {
    GridPaginate,
    Grid
  },
  props: {
    url: String,
    page: Number,
    limit: Number,
  },
  data() {
    return {
      searchQuery: '',
      gridColumns: [],
      gridData: [],
      sortOrders: [],
      nbElements: 0,
      loading: true,
      cPage: this.page,
      cLimit : this.limit,
      listLimit: {},
      translate: {},
      translateGridPaginate : {},
      translateGrid: {}
    }
  },
  mounted() {
    this.loadData(this.page, this.limit);
  },
  methods: {
    /**
     * Chargement des elements du tableau
     * @param page
     * @param limit
     */
    loadData(page, limit) {
      this.loading = true;
      axios.post(this.url, {
        page: page,
        limit: limit
      }).then((response) => {
        this.gridColumns = response.data.column;
        this.gridData = response.data.data;
        this.nbElements = response.data.nb;
        this.sortOrders = this.gridColumns.reduce((o, key) => ((o[key] = 1), o), {});
        this.listLimit = JSON.parse(response.data.listLimit);
        this.translate = JSON.parse(response.data.translate.genericGrid);
        this.translateGridPaginate = JSON.parse(response.data.translate.gridPaginate);
        this.translateGrid = JSON.parse(response.data.translate.grid);
        this.cPage = page;
        this.cLimit = limit;
      }).catch((error) => {
        console.log(error);
      }).finally(() => this.loading = false);
    },

    /**
     * Défini l'action à faire en fonction des paramètres
     * @param url
     * @param id
     * @param is_ajax
     */
    redirectAction(url, id, is_ajax) {
      console.log(url);
      console.log(id);
      console.log(is_ajax);

      if (is_ajax) {
        alert('appel ajax');
        this.loading = true;
        this.loadData(this.cPage, this.cLimit);
      } else {
        window.location.href = url;
      }
    },
  }
}

</script>

<template>
  <form id="search">
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" :placeholder="translate.placeholder" v-model="searchQuery">
    </div>
  </form>
  <div :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loading }}</span>
      </div>
    </div>
    <Grid
        :data="gridData"
        :columns="gridColumns"
        :filter-key="searchQuery"
        :sortOrders="sortOrders"
        :translate="translateGrid"
        @redirect-action="redirectAction">
    </Grid>
    <GridPaginate
        :current-page="page"
        :nb-elements="limit"
        :nb-elements-total="nbElements"
        :url="url"
        :list-limit="listLimit"
        :translate="translateGridPaginate"
        @change-page-event="loadData"
    >
    </GridPaginate>
  </div>

</template>

<style scoped>
</style>
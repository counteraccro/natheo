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
      loading: true
    }
  },
  mounted() {
    this.loadData(this.page);
  },
  methods: {
    /**
     * Chargement des elements du tableau
     * @param page
     */
    loadData(page) {
      this.loading = true;
      axios.post(this.url, {
        page: page,
        limit: this.limit
      }).then((response) => {
        this.gridColumns = response.data.column;
        this.gridData = response.data.data;
        this.nbElements = response.data.nb;
        this.sortOrders = this.gridColumns.reduce((o, key) => ((o[key] = 1), o), {});
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
        this.loadData(1);
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
      <input type="text" class="form-control" placeholder="Rechercher" v-model="searchQuery">
    </div>
  </form>

  <div :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <span class="txt-overlay">Chargement des données</span>
      </div>
    </div>
    <Grid
        :data="gridData"
        :columns="gridColumns"
        :filter-key="searchQuery"
        :sortOrders="sortOrders"
        @redirect-action="redirectAction">
    </Grid>
    <GridPaginate
        :current-page="page"
        :nb-elements="limit"
        :nb-elements-total="nbElements"
        :url="url"
        @change-page-event="loadData"
    >
    </GridPaginate>
  </div>

</template>

<style scoped>
</style>
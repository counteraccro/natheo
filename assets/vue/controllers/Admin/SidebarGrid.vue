<script>
import Grid from '../../Components/Grid/Grid.vue'
import GridPaginate from "../../Components/Grid/GridPaginate.vue";
import axios from "axios";

export default {
  name: "SidebarGrid",
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
      nbElements: 0
    }
  },
  mounted() {
    this.loadData(this.page);
  },
  methods: {
    loadData(page) {
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
      });
    },

    /**
     * Défini l'action à faire en fonction des paramètres
     * @param url
     * @param id
     * @param is_ajax
     */
    redirectAction(url, id, is_ajax)
    {

      console.log(url);
      console.log(id);
      console.log(is_ajax);

      if(is_ajax)
      {
        alert('appel ajax');
        this.loadData(1);
      }
      else {
        window.location.href = url;
      }
    },
  }
}

</script>

<template>
  <form id="search">
    Search <input name="query" v-model="searchQuery">
  </form>
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
</template>

<style scoped>

</style>
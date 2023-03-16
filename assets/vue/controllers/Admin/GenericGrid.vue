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
      translateGrid: {},
      msgSuccess: '',
      showMsgSuccess: false
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
    redirectAction(url,is_ajax) {

      if (is_ajax) {
        axios.post(url).then((response) => {
          if(response.data.type === 'success')
          {
            this.msgSuccess = response.data.msg;
            this.showMsgSuccess = true;
            setTimeout(() => {
              this.showMsgSuccess = false;
            }, 3000)
          }
        }).catch((error) => {
          console.log(error);
        }).finally(() => this.loadData(this.cPage, this.cLimit));
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


  <div v-if="this.showMsgSuccess" class="alert alert-success alert-dismissible">
    <strong><i class="bi bi-check2-circle"></i> {{ translate.titleSuccess }} </strong> <br />
    <span v-html="this.msgSuccess"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

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

.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>
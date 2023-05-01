<script>
import Grid from '../../Components/Grid/Grid.vue'
import GridPaginate from "../../Components/Grid/GridPaginate.vue";
import axios from "axios";
import {Modal} from 'bootstrap'

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
      cLimit: this.limit,
      cUrl: '',
      isAjax: '',
      listLimit: {},
      translate: {},
      translateGridPaginate: {},
      translateGrid: {},
      msgSuccess: '',
      showMsgSuccess: false,
      confirmModal: '',
      msgConfirm: '',
    }
  },
  mounted() {
    this.loadData(this.page, this.limit);
    this.confirmModal = new Modal(document.getElementById("popin-alert"), {});

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
     * @param is_confirm
     * @param is_ajax
     * @param msg_confirm
     */
    redirectAction(url, is_ajax, is_confirm, msg_confirm) {

      this.cUrl = url;
      this.isAjax = is_ajax;
      this.msgConfirm = this.translate.confirmText;
      this.confirmModal.hide();

      if (is_confirm) {
        this.msgConfirm = msg_confirm;
        this.confirmModal.show();
      } else {
        if (is_ajax) {
          this.loading = true;
          axios.post(url).then((response) => {
            if (response.data.type === 'success') {
              this.msgSuccess = response.data.msg;
              this.showMsgSuccess = true;
              setTimeout(() => {
                this.showMsgSuccess = false;
              }, 5000)
            }
          }).catch((error) => {
            console.log(error);
          }).finally(() => this.loadData(this.cPage, this.cLimit));
        } else {
          window.location.href = url;
        }
      }
    },
  }
}

</script>

<template>
  <form id="search">
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control no-control" :placeholder="translate.placeholder" v-model="searchQuery">
    </div>
  </form>


  <div v-if="this.showMsgSuccess" class="alert alert-success alert-dismissible">
    <strong><i class="bi bi-check2-circle"></i> {{ translate.titleSuccess }} </strong> <br/>
    <span v-html="this.msgSuccess"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <div class="modal fade" id="popin-alert" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white"><i class="bi bi-sign-stop"></i> {{ translate.confirmTitle }}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" v-html="msgConfirm">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" @click="redirectAction(this.cUrl, this.isAjax, false, '')"><i class="bi bi-check2-circle"></i> {{ translate.confirmBtnOK }}</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> {{ translate.confirmBtnNo }}</button>
        </div>
      </div>
    </div>
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
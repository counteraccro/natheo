<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le tableau GRID
 */

import Grid from '../../Components/Grid/Grid.vue'
import GridPaginate from "../../Components/Grid/GridPaginate.vue";
import axios from "axios";
import {Modal, Toast} from 'bootstrap'

export default {
  name: "GenericGrid",
  components: {
    GridPaginate,
    Grid
  },
  props: {
    url: String,
    page: Number,
    limit: String,
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
      confirmModal: '',
      msgConfirm: '',
      toasts: {
        success: {
          toast: [],
          msg: '',
        },
        error: {
          toast: [],
          msg: '',
        }
      },
    }
  },
  mounted() {

    let toastSuccess = document.getElementById('live-toast-success');
    this.toasts.success.toast = Toast.getOrCreateInstance(toastSuccess);

    let toastError = document.getElementById('live-toast-error');
    this.toasts.error.toast = Toast.getOrCreateInstance(toastError);

    this.loadData(this.page, this.limit);
    this.confirmModal = new Modal(document.getElementById("modal-alert"), {});

  },
  methods: {
    /**
     * Chargement des elements du tableau
     * @param page
     * @param limit
     */
    loadData(page, limit) {
      this.loading = true;
      axios.get(this.url + '/' + page + '/' + limit).then((response) => {
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
        console.error(error);
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
            if (response.data.success === true || response.data.type === 'success') {

              if(response.data.type === 'success')
              {
                alert('Ancient système de retour de la réponse, à changer pour l\'url ' + url + ' \n ' +
                    'Voir Controller/Admin/Content/FaqController::updateDisabled pour un exemple de la bonne pratique');
              }

              this.toasts.success.msg = response.data.msg;
              this.toasts.success.toast.show();
            }
            else {
              this.toasts.error.msg = response.data.msg;
              this.toasts.error.toast.show();
            }
          }).catch((error) => {
            console.error(error);
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

  <!-- A supprimer à terme si tout est OK - ancien système d'affichage des messages
  <div v-if="this.showMsgSuccess" class="alert alert-success alert-dismissible">
    <strong><i class="bi bi-check2-circle"></i> {{ translate.titleSuccess }} </strong> <br/>
    <span v-html="this.msgSuccess"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>-->

  <div class="modal fade" id="modal-alert" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
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

  <div class="toast-container position-fixed top-0 end-0 p-2">

    <div id="live-toast-success" class="toast border border-secondary bg-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header text-success">
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{translate.titleSuccess }}</strong>
        <small class="text-black-50">{{ translate.time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" v-html="this.toasts.success.msg"></div>
    </div>

    <div id="live-toast-error" class="toast border border-secondary bg-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header text-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ translate.titleError }}</strong>
        <small class="text-black-50">{{ translate.time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" v-html="this.toasts.error.msg"></div>
    </div>
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
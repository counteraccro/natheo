<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher les logs sous forme de tableau d'après les fichiers de logs
 */
import Grid from '../../../Components/Grid/Grid.vue'
import GridPaginate from "../../../Components/Grid/GridPaginate.vue";
import axios from "axios";
import Modal from "../../../Components/Global/Modal.vue";
import Toast from "../../../Components/Global/Toast.vue";
import {emitter} from "../../../../utils/useEvent";

export default {
  name: "Log",
  components: {
    Toast,
    Modal,
    GridPaginate,
    Grid
  },
  props: {
    url_select: String,
    url_load_log_file: String,
    url_delete_file: String,
    page: Number,
    limit: String,
  },
  data() {
    return {
      select: [],
      time: "all",
      trans: [],
      searchQuery: '',
      gridColumns: [],
      gridData: [],
      sortOrders: [],
      nbElements: 0,
      loading: true,
      cPage: this.page,
      cLimit: this.limit,
      cUrl: '',
      listLimit: {},
      translate: {},
      translateGridPaginate: {},
      translateGrid: {},
      msgConfirm: '',
      selectFile: '',
      taille: 0,
      loadDeleteFile: false,
      modalDeleteLog: false,
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      }
    }
  },
  mounted() {
    this.loadData();
  }
  ,
  methods: {
    loadData() {
      this.loading = true;
      axios.get(this.url_select + '/' + this.time).then((response) => {
        this.select = response.data.files;
        this.trans = response.data.trans;
      }).catch((error) => {
        console.error(error);
      }).finally(() => this.loading = false);
    }
    ,
    changeTimeFiltre(event) {
      this.time = event.target.value;
      this.selectFile = '';
      this.loadData();
    }
    ,

    /**
     * Charge le contenu d'un fichier log
     * @param event
     */
    selectLogFile(event) {
      this.selectFile = event.target.value;
      if (this.selectFile !== "") {
        this.loadContentFile(this.page, this.limit);
      }
    }
    ,

    /**
     * Charge le contenu d'un log
     * @param page
     * @param limit
     */
    loadContentFile(page, limit) {
      this.loading = true;
      axios.get(this.url_load_log_file + '/' + this.selectFile + '/' + page + '/' + limit).then((response) => {

        if (response.data.success === true) {
          if(page === 1) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          }

          this.gridColumns = response.data.grid.column;
          this.gridData = response.data.grid.data;
          this.nbElements = response.data.grid.nb;
          this.sortOrders = this.gridColumns.reduce((o, key) => ((o[key] = 1), o), {});
          this.listLimit = JSON.parse(response.data.grid.listLimit);
          this.translate = JSON.parse(response.data.grid.translate.genericGrid);
          this.translateGridPaginate = JSON.parse(response.data.grid.translate.gridPaginate);
          this.translateGrid = JSON.parse(response.data.grid.translate.grid);
          this.cPage = page;
          this.cLimit = limit;
          this.taille = response.data.grid.taille;

        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false;
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => this.loading = false);
    }
    ,

    /**
     * Supprimer un fichier
     * @param file
     * @param confirm
     * @returns {boolean}
     */
    delete(file, confirm) {

      if (confirm) {
        this.showModal();
        this.msgConfirm = this.trans.log_delete_file_confirm + ' <b>' + this.selectFile + '</b> ? <br /> ' + this.trans.log_delete_file_confirm_2;
        return false;
      }

      this.hideModal();
      this.loading = true;

      axios.delete(this.url_delete_file + '/' + file, {}).then((response) => {

        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;

          document.getElementById('select-time').getElementsByTagName('option')[0].selected = 'selected';
          document.getElementById('select-file').getElementsByTagName('option')[0].selected = 'selected';
          this.time = '';
          this.selectFile = '';
          this.loadDeleteFile = false;
          this.loadData();

        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false;
        }

      }).catch((error) => {
        console.error(error);
      }).finally();
    }
    ,

    /**
     * Affichage la modale
     */
    showModal() {
      this.modalDeleteLog = true;
    }
    ,

    /**
     * Ferme la modale
     */
    hideModal() {
      this.modalDeleteLog = false;
    }
    ,

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    }
    ,
  }
}
</script>

<template>
  <div :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loading }}</span>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <select class="form-select no-control" id="select-file" @change="selectLogFile($event)">
          <option value="" selected>{{ this.trans.log_select_file }}</option>
          <option v-for="option in this.select" v-bind:value="option.path">{{ option.name }}</option>
        </select>
      </div>
      <div class="col">
        <select class="form-select no-control" id="select-time" @change="changeTimeFiltre($event)">
          <option value="all">{{ this.trans.log_select_time_all }}</option>
          <option value="now">{{ this.trans.log_select_time_now }}</option>
          <option value="yesterday">{{ this.trans.log_select_time_yesterday }}</option>
        </select>
      </div>
    </div>

    <div v-if="selectFile !== ''">

      <div class="card mt-3 border border-secondary">
        <div class="card-header text-bg-secondary">

          <div class="dropdown float-end">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="#" @click="this.loadContentFile(1, this.limit)"><i class="bi bi-arrow-clockwise"></i> {{ this.trans.log_btn_reload }}</a>
              </li>
              <li>
                <a class="dropdown-item" href="#"><i class="bi bi-download"></i> {{ this.trans.log_btn_download_file }}</a>
              </li>
              <li>
                <a class="dropdown-item" href="#" @click="this.delete(this.selectFile, true)"><i class="bi bi-x-lg"></i> {{ this.trans.log_btn_delete_file }}</a>
              </li>
            </ul>
          </div>

          <div class="mt-1"><i class="bi bi-file-earmark-text"></i>
            {{ this.trans.log_file }}
            <b>{{ this.selectFile }}</b> - {{ this.trans.log_file_size }} {{ this.taille }} - {{ this.nbElements }} {{ this.trans.log_file_ligne }}
          </div>

        </div>
        <div class="card-body">

          <form id="search">
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control no-control" :placeholder="translate.placeholder" v-model="searchQuery">
            </div>
          </form>

          <div>

            <Grid
                :data="gridData"
                :columns="gridColumns"
                :filter-key="searchQuery"
                :sortOrders="sortOrders"
                :translate="translateGrid"
                @redirect-action="redirectAction">
            </Grid>
            <GridPaginate
                :current-page="cPage"
                :nb-elements="limit"
                :nb-elements-total="nbElements"
                :url="url_load_log_file"
                :list-limit="listLimit"
                :translate="translateGridPaginate"
                @change-page-event="loadContentFile"
            >
            </GridPaginate>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="card mt-3 border border-secondary">
      <div class="card-header text-bg-secondary">
        <div class="btn btn-secondary btn-sm float-end disabled"><i class="bi bi-list"></i></div>
        <div class="mt-1">
          <i class="bi bi-file-earmark-text"></i> {{ this.trans.log_file }} -- - {{ this.trans.log_file_size }} 0 Ko - 0 {{ this.trans.log_file_ligne }}
        </div>
      </div>
      <div class="card-body">
        <p class="text-center"><i class="bi bi-info-circle"></i> <i>{{ this.trans.log_empty_file }}</i></p>
      </div>
    </div>
  </div>

  <!-- modale confirmation suppression -->
  <modal
      :id="'modalDeleteLog'"
      :show="this.modalDeleteLog"
      @close-modal="this.hideModal"
      :option-show-close-btn="false">
    <template #title>
      <i class="bi bi-sign-stop"></i> {{ translate.confirmTitle }}
    </template>
    <template #body>
      <div v-html="this.msgConfirm"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary" @click="this.delete(this.selectFile, false)">
        <i class="bi bi-check2-circle"></i> {{ translate.confirmBtnOK }}
      </button>
      <button type="button" class="btn btn-secondary" @click="this.hideModal">
        <i class="bi bi-x-circle"></i> {{ translate.confirmBtnNo }}
      </button>
    </template>
  </modal>
  <!-- fin modale confirmation supression -->

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.trans.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.trans.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastError'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastError.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.trans.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.trans.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>

</template>
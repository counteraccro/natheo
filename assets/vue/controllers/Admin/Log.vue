<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher les logs sous forme de tableau d'après les fichiers de logs
 */
import Grid from '../../Components/Grid/Grid.vue'
import GridPaginate from "../../Components/Grid/GridPaginate.vue";
import axios from "axios";
import {Modal} from 'bootstrap'

export default {
  name: "Log",
  components: {
    GridPaginate,
    Grid
  },
  props: {
    url_select: String,
    url_load_log_file: String,
    url_delete_file: String,
    page: Number,
    limit: Number,
  },
  data() {
    return {
      select: [],
      time: "",
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
      isAjax: '',
      listLimit: {},
      translate: {},
      translateGridPaginate: {},
      translateGrid: {},
      msgSuccess: '',
      showMsgSuccess: false,
      confirmModal: '',
      msgConfirm: '',
      selectFile: '',
      taille: 0,
      loadDeleteFile: false,
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      axios.post(this.url_select, {
        'time': this.time
      }).then((response) => {
        this.select = response.data.files;
        this.trans = response.data.trans;
      }).catch((error) => {
        console.log(error);
      }).finally();
    },
    changeTimeFiltre(event) {
      this.time = event.target.value;
      this.selectFile = '';
      this.loadData();
    },

    /**
     * Charge le contenu d'un fichier log
     * @param event
     */
    selectLogFile(event) {
      this.selectFile = event.target.value;
      if (this.selectFile !== "") {
        this.loadContentFile(1, 50);
      }
    },

    /**
     * Charge le contenu d'un log
     * @param page
     * @param limit
     */
    loadContentFile(page, limit) {
      this.loading = true;
      axios.post(this.url_load_log_file, {
        file: this.selectFile,
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
        this.taille = response.data.taille;
      }).catch((error) => {
        console.log(error);
      }).finally(() => this.loading = false);
    },

    /**
     * Supprimer un fichier
     * @param file
     * @param confirm
     * @returns {boolean}
     */
    delete(file, confirm) {

      if (confirm) {
        this.confirmModal = new Modal(document.getElementById("staticBackdrop"), {});
        this.msgConfirm = this.trans.log_delete_file_confirm + ' <b>' + this.selectFile + '</b> ' +this.trans.log_delete_file_confirm_2;
        this.confirmModal.show();
        return false;
      }

      this.loadDeleteFile = true;
      this.msgConfirm = this.trans.log_delete_file_loading;

      axios.post(this.url_delete_file, {
        'file': file
      }).then((response) => {

        if(response.data.success) {
          this.msgConfirm = this.trans.log_delete_file_success;
          this.confirmModal.hide();

          document.getElementById('select-time').getElementsByTagName('option')[0].selected = 'selected';
          document.getElementById('select-file').getElementsByTagName('option')[0].selected = 'selected';
          this.time = '';
          this.selectFile = '';
          this.loadData();
        }
        else {
          alert('Un erreur est survenue lors de la suppression');
        }
      }).catch((error) => {
        console.log(error);
      }).finally();
    },

    redirectAction() {
    }
  }
}
</script>

<template>
  <div class="row">
    <div class="col">
      <select class="form-select" id="select-file" @change="selectLogFile($event)">
        <option value="" selected>{{ this.trans.log_select_file }}</option>
        <option v-for="option in this.select" v-bind:value="option.path">{{ option.name }}</option>
      </select>
    </div>
    <div class="col">
      <select class="form-select" id="select-time" @change="changeTimeFiltre($event)">
        <option value="">{{ this.trans.log_select_time_all }}</option>
        <option value="now">{{ this.trans.log_select_time_now }}</option>
        <option value="yesterday">{{ this.trans.log_select_time_yesterday }}</option>
      </select>
    </div>
  </div>

  <div v-if="selectFile !== ''">

    <div class="card mt-3">
      <div class="card-header text-bg-secondary">
        <div class="btn btn-danger btn-sm float-end" @click="this.delete(this.selectFile, true)">{{ this.trans.log_btn_delete_file }}</div>
        {{ this.trans.log_file }} {{ this.selectFile }} - {{ this.trans.log_file_size }} {{ this.taille }} - {{ this.nbElements }} {{ this.trans.log_file_ligne }}

      </div>
      <div class="card-body">

        <form id="search">
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" :placeholder="translate.placeholder" v-model="searchQuery">
          </div>
        </form>


        <div v-if="this.showMsgSuccess" class="alert alert-success alert-dismissible">
          <strong><i class="bi bi-check2-circle"></i> {{ translate.titleSuccess }} </strong> <br/>
          <span v-html="this.msgSuccess"></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-secondary">
                <h1 class="modal-title fs-5 text-white"><i class="bi bi-sign-stop"></i> {{ translate.confirmTitle }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" v-html="msgConfirm">
              </div>
              <div class="modal-footer" v-if="!loadDeleteFile">
                <button type="button" class="btn btn-primary" @click="this.delete(this.selectFile, false)">
                  <i class="bi bi-check2-circle"></i> {{ translate.confirmBtnOK }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="bi bi-x-circle"></i> {{ translate.confirmBtnNo }}
                </button>
              </div>
              <div class="modal-footer" v-else>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="bi bi-x-circle"></i> {{ trans.log_delete_file_btn_close }}
                </button>
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
  <div class="card mt-3" v-else>
    <div class="card-header text-bg-secondary">
      <div class="btn btn-danger btn-sm float-end disabled">{{ this.trans.log_btn_delete_file }}</div>
      {{ this.trans.log_file }} -- - {{ this.trans.log_file_size }} 0 Ko - 0 {{ this.trans.log_file_ligne }}
    </div>
    <div class="card-body">
      <p class="text-center"><i class="bi bi-info-circle"></i> <i>{{ this.trans.log_empty_file }}</i></p>
    </div>
  </div>

</template>

<style scoped>

</style>
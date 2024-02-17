<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le tableau GRID
 */

import Grid from '../../Components/Grid/Grid.vue'
import GridPaginate from "../../Components/Grid/GridPaginate.vue";
import axios from "axios";
import Modal from "../../Components/Global/Modal.vue";
import Toast from "../../Components/Global/Toast.vue";

export default {
  name: "GenericGrid",
  components: {
    GridPaginate,
    Grid,
    Modal,
    Toast
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
      httpType: '',
      listLimit: {},
      translate: {},
      translateGridPaginate: {},
      translateGrid: {},
      showModalGenericGrid: false,
      msgConfirm: '',
      toasts: {
        toastSuccessGenericGrid: {
          show: false,
          msg: '',
        },
        toastErrorGenericGrid: {
          show: false,
          msg: '',
        }
      },
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
     * @param type
     */
    redirectAction(url, is_ajax, is_confirm, msg_confirm, type) {

      this.cUrl = url;
      this.isAjax = is_ajax;
      this.httpType = type;
      this.msgConfirm = this.translate.confirmText;
      this.hideModal();

      if (is_confirm) {
        this.msgConfirm = msg_confirm;
        this.showModal();
      } else {
        if (is_ajax) {
          this.loading = true;

          if (type === undefined) {
            type = 'post';
            console.error('URL ' + url + ' n\'a aucun type défini');
          }

          axios[type](url).then((response) => {
            if (response.data.success === true || response.data.type === 'success') {

              if (response.data.type === 'success') {
                console.error('Ancient système de retour de la réponse, à changer pour l\'url ' + url + ' \n ' +
                    'Voir Controller/Admin/Content/FaqController::updateDisabled pour un exemple de la bonne pratique');
              }

              this.toasts.toastSuccessGenericGrid.msg = response.data.msg;
              this.toasts.toastSuccessGenericGrid.show = true;
            } else {
              this.toasts.toastErrorGenericGrid.msg = response.data.msg;
              this.toasts.toastErrorGenericGrid.show;
            }
          }).catch((error) => {
            console.error(error);
          }).finally(() => this.loadData(this.cPage, this.cLimit));
        } else {
          window.location.href = url;
        }
      }
    },

    /**
     * Affichage la modale
     */
    showModal() {
      this.showModalGenericGrid = true;
    },

    /**
     * Ferme la modale
     */
    hideModal() {
      this.showModalGenericGrid = false;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast)
    {
      this.toasts[nameToast].show = false
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

  <modal
      :id="'generic-grid-modale'"
      :show="this.showModalGenericGrid"
      @close-modal="this.hideModal"
      :option-show-close-btn="false"
  >
    <template #title>
      <i class="bi bi-sign-stop"></i> {{ translate.confirmTitle }}
    </template>
    <template #body>
      <div v-html="msgConfirm"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary" @click="redirectAction(this.cUrl, this.isAjax, false, '', this.httpType)">
        <i class="bi bi-check2-circle"></i> {{ translate.confirmBtnOK }}
      </button>
      <button type="button" class="btn btn-secondary" @click="this.hideModal()">
        <i class="bi bi-x-circle"></i> {{ translate.confirmBtnNo }}
      </button>
    </template>
  </modal>

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
    <toast
        :id="'toastSuccessGenericGrid'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccessGenericGrid.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ translate.titleSuccess }}</strong>
        <small class="text-black-50">{{ translate.time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccessGenericGrid.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastErrorGenericGrid'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastErrorGenericGrid.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ translate.titleError }}</strong>
        <small class="text-black-50">{{ translate.time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastErrorGenericGrid.msg"></div>
      </template>
    </toast>
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
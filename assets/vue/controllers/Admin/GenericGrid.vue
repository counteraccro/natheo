<script>
/**
 * @author Gourdon Aymeric
 * @version 1.5
 * Permet de générer le tableau GRID
 */

import Grid from '../../Components/Grid/Grid.vue';
import GridPaginate from '../../Components/Grid/GridPaginate.vue';
import axios from 'axios';
import Modal from '../../Components/Global/Modal.vue';
import Toast from '../../Components/Global/Toast.vue';
import { copyToClipboard } from '../../../utils/copyToClipboard';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';

export default {
  name: 'GenericGrid',
  components: {
    SkeletonTable,
    GridPaginate,
    Grid,
    Modal,
    Toast,
  },
  props: {
    url: String,
    page: Number,
    limit: String,
    activeSearchData: {
      type: Boolean,
      default: false,
    },
    showFilter: {
      type: Boolean,
      default: false,
    },
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
      searchMode: 'table',
      searchPlaceholder: '',
      cQuery: '',
      showQuery: false,
      urlSaveQuery: '',
      filter: 'all',
      filterIcon: 'bi-people-fill',
      toasts: {
        toastSuccessGenericGrid: {
          show: false,
          msg: '',
        },
        toastErrorGenericGrid: {
          show: false,
          msg: '',
        },
      },
    };
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

      let strSearch = this.getSearchParams();
      let filter = this.getFilterParams();
      axios
        .get(this.url + '/' + page + '/' + limit + filter + strSearch)
        .then((response) => {
          this.gridColumns = response.data.column;
          this.gridData = response.data.data;
          this.nbElements = response.data.nb;
          this.sortOrders = this.gridColumns.reduce((o, key) => ((o[key] = 1), o), {});
          this.listLimit = response.data.listLimit;
          this.translate = response.data.translate.genericGrid;
          this.translateGridPaginate = response.data.translate.gridPaginate;
          this.translateGrid = response.data.translate.grid;
          this.cPage = page;
          this.cLimit = limit;
          this.urlSaveQuery = response.data.urlSaveSql;

          if (response.data.sql !== undefined) {
            this.cQuery = response.data.sql;
          }

          this.searchPlaceholder = this.translate.placeholder;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => (this.loading = false));
    },

    /**
     * Génération du paramètre filtre
     * @return {string}
     */
    getFilterParams() {
      return '?filter=' + this.filter;
    },

    /**
     * Génération du paramètre de recherche
     * @return {string}
     */
    getSearchParams() {
      if (this.searchMode !== 'table') {
        return '&search=' + this.searchQuery;
      }
      return '';
    },

    /**
     * Rechargement de la page
     */
    reloadData() {
      this.loadData(this.page, this.cLimit);
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
            console.error('URL ' + url + " n'a aucun type défini");
          }

          axios[type](url)
            .then((response) => {
              if (response.data.success === true || response.data.type === 'success') {
                if (response.data.type === 'success') {
                  console.error(
                    "Ancient système de retour de la réponse, à changer pour l'url " +
                      url +
                      ' \n ' +
                      'Voir Controller/Admin/Content/FaqController::updateDisabled pour un exemple de la bonne pratique'
                  );
                }

                this.toasts.toastSuccessGenericGrid.msg = response.data.msg;
                this.toasts.toastSuccessGenericGrid.show = true;
              } else {
                this.toasts.toastErrorGenericGrid.msg = response.data.msg;
                this.toasts.toastErrorGenericGrid.show = true;
              }
            })
            .catch((error) => {
              console.error(error);
            })
            .finally(() => this.loadData(this.cPage, this.cLimit));
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
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },

    /**
     * Permet de changer de mode de recherche
     * @param mode
     */
    changeSearchMode(mode) {
      this.searchMode = mode;
      if (this.searchMode === 'table') {
        this.searchPlaceholder = this.translate.placeholder;
      } else {
        this.searchPlaceholder = this.translate.placeholderBddSearch;
      }

      return false;
    },

    /**
     * Affiche la requete SQL
     * @param bool
     */
    showQueryRun(bool) {
      this.showQuery = bool;
    },

    /**
     * Fait un copier coller
     */
    async copyQueryRun() {
      try {
        await copyToClipboard(this.cQuery);
        this.toasts.toastSuccessGenericGrid.msg = this.translate.copySuccess;
        this.toasts.toastSuccessGenericGrid.show = true;
      } catch (error) {
        this.toasts.toastErrorGenericGrid.msg = this.translate.copyError;
        this.toasts.toastErrorGenericGrid.show = true;
      }
    },

    saveQueryRun() {
      this.loading = true;
      axios
        .post(this.urlSaveQuery, {
          query: this.cQuery,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccessGenericGrid.msg = response.data.msg;
            this.toasts.toastSuccessGenericGrid.show = true;
          } else {
            this.toasts.toastErrorGenericGrid.msg = response.data.msg;
            this.toasts.toastErrorGenericGrid.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.loadData(this.page, this.limit);
        });
    },

    /**
     * Changement du filtre
     * @param filterChange
     */
    changeFilter(filterChange) {
      let filter = '';
      let icon = '';
      switch (filterChange) {
        case 'me':
          filter = 'me';
          icon = 'bi-person-fill';
          break;
        case 'all':
          filter = 'all';
          icon = 'bi-people-fill';
          break;
        default:
          filter = 'all';
          icon = 'bi-people-fill';
      }
      this.filter = filter;
      this.filterIcon = icon;
      this.loadData(1, this.limit);
    },
  },
};
</script>

<template>
  <div class="card rounded-lg mt-4 p-2">
    <form id="search">
      <div v-if="this.showQuery" class="card mb-4">
        <div class="card-header">
          <div class="float-end">
            <div class="btn btn-secondary btn-sm m-1 mt-0" @click="this.saveQueryRun"><i class="bi bi-save"></i></div>
            <div class="btn btn-secondary btn-sm m-1 mt-0" @click="this.copyQueryRun">
              <i class="bi bi-clipboard"></i>
            </div>
            <div class="btn btn-secondary btn-sm m-1 mt-0" @click="this.showQueryRun(false)">
              <i class="bi bi-x"></i>
            </div>
          </div>
          {{ this.translate.queryTitle }}
        </div>
        <div class="card-body">
          {{ this.cQuery }}
        </div>
      </div>

      <div class="row">
        <div class="col-10">
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input
              type="text"
              class="form-control no-control"
              :placeholder="this.searchPlaceholder"
              v-model="searchQuery"
            />
            <button
              :disabled="!this.activeSearchData"
              v-if="this.searchMode === 'bdd'"
              type="button"
              @click="this.loadData(this.cPage, this.cLimit)"
              class="btn btn-secondary"
            >
              {{ this.translate.btnSearch }}
            </button>
            <button
              :disabled="!this.activeSearchData"
              v-if="this.activeSearchData"
              type="button"
              class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="#" @click="this.changeSearchMode('table')"
                  ><i class="bi bi-table"></i> {{ this.translate.placeholder }}</a
                >
              </li>
              <li>
                <a class="dropdown-item" href="#" @click="this.changeSearchMode('bdd')"
                  ><i class="bi bi-database-down"></i> {{ this.translate.placeholderBddSearch }}</a
                >
              </li>
            </ul>
          </div>
        </div>
        <div class="col-2">
          <div
            v-if="this.showFilter"
            class="btn btn-secondary dropdown-toggle m-1 mt-0"
            href="#"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="bi bi-filter-circle"></i> <i class="bi" :class="this.filterIcon"></i>
          </div>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item no-control" href="#" @click="this.changeFilter('me')"
                ><i class="bi bi-person-fill"></i> {{ this.translate.filterOnlyMe }}</a
              >
            </li>
            <li>
              <a class="dropdown-item no-control" href="#" @click="this.changeFilter('all')"
                ><i class="bi bi-people-fill"></i> {{ this.translate.filterAll }}</a
              >
            </li>
          </ul>

          <div class="btn btn-secondary m-1 mt-0" @click="this.reloadData"><i class="bi bi-arrow-clockwise"></i></div>
          <div v-if="this.cQuery !== ''" class="btn btn-secondary m-1 mt-0" @click="this.showQueryRun(true)">
            <i class="bi bi-database"></i>
          </div>
        </div>
      </div>
    </form>

    <div v-if="this.loading">
      <SkeletonTable :rows="5" :columns="5" />
    </div>
    <div v-else>
      <Grid
        :data="gridData"
        :columns="gridColumns"
        :filter-key="searchQuery"
        :sortOrders="sortOrders"
        :translate="translateGrid"
        :search-mode="this.searchMode"
        @redirect-action="redirectAction"
      >
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
  </div>

  <modal
    :id="'generic-grid-modale'"
    :show="this.showModalGenericGrid"
    @close-modal="this.hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.confirmTitle }} </template>
    <template #body>
      <div v-html="msgConfirm"></div>
    </template>
    <template #footer>
      <button
        type="button"
        class="btn btn-primary btn-sm me-2"
        @click="redirectAction(this.cUrl, this.isAjax, false, '', this.httpType)"
      >
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>
        {{ translate.confirmBtnOK }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="this.hideModal()">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.confirmBtnNo }}
      </button>
    </template>
  </modal>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccessGenericGrid'"
      :type="'success'"
      :show="this.toasts.toastSuccessGenericGrid.show"
      @close-toast="this.closeToast"
    >
      <template #body>
        <div v-html="this.toasts.toastSuccessGenericGrid.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastErrorGenericGrid'"
      :type="'danger'"
      :show="this.toasts.toastErrorGenericGrid.show"
      @close-toast="this.closeToast"
    >
      <template #body>
        <div v-html="this.toasts.toastErrorGenericGrid.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style scoped></style>

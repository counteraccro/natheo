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
import { emitter } from '../../../utils/useEvent';

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
      btnSearchMode: '',
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

    this.btnSearchMode = new Dropdown(
      document.getElementById('searchMode'),
      document.getElementById('searchModeButton')
    );
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

      let tmp = this.url.split('/');
      let url = this.url + '/' + page + '/' + limit + filter + strSearch;
      if (tmp.length > 6) {
        url =
          tmp[0] +
          '/' +
          tmp[1] +
          '/' +
          tmp[2] +
          '/' +
          tmp[3] +
          '/' +
          tmp[4] +
          '/' +
          tmp[5] +
          '/' +
          page +
          '/' +
          limit +
          filter +
          strSearch;
      }

      axios
        .get(url)
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
      this.btnSearchMode.hide();

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
  <div>
    <div class="card rounded-lg mt-8 p-4 mb-6">
      <form id="search">
        <div class="flex flex-col lg:flex-row gap-4 items-center">
          <div class="flex-1">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg
                  class="w-5 h-5"
                  style="color: var(--text-light)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  ></path>
                </svg>
              </div>
              <input
                type="search"
                class="form-input input-icon-left"
                :placeholder="this.searchPlaceholder"
                v-model="searchQuery"
              />
            </div>
          </div>

          <div class="flex gap-2">
            <button
              :disabled="!this.activeSearchData"
              v-if="this.searchMode === 'bdd'"
              type="button"
              @click="this.loadData(this.cPage, this.cLimit)"
              class="btn btn-outline-primary btn-md"
            >
              <svg
                class="icon-sm"
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
                  stroke-width="2"
                  d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                />
              </svg>

              {{ this.translate.btnSearch }}
            </button>

            <button id="searchModeButton" data-dropdown-open="searchMode" class="btn btn-outline-primary btn-md">
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                ></path>
              </svg>
              Filtrer
            </button>

            <div
              id="searchMode"
              class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-auto border-1 border-gray-200"
            >
              <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
                <li>
                  <a
                    href="#"
                    class="no-control px-4 py-2 hover:bg-gray-100 flex item-center"
                    @click="this.changeSearchMode('table')"
                  >
                    <svg
                      class="w-5 h-5 mr-3 text-[var(--primary)]"
                      aria-hidden="true"
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke="currentColor"
                        stroke-width="2"
                        d="M3 11h18M3 15h18m-9-4v8m-8 0h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"
                      />
                    </svg>

                    {{ this.translate.placeholder }}</a
                  >
                </li>
                <li>
                  <a
                    href="#"
                    class="no-control flex items-center px-4 py-2 hover:bg-gray-100"
                    @click="this.changeSearchMode('bdd')"
                  >
                    <svg
                      class="w-5 h-5 mr-3 text-[var(--primary)]"
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
                        d="M19 6c0 1.657-3.134 3-7 3S5 7.657 5 6m14 0c0-1.657-3.134-3-7-3S5 4.343 5 6m14 0v6M5 6v6m0 0c0 1.657 3.134 3 7 3s7-1.343 7-3M5 12v6c0 1.657 3.134 3 7 3s7-1.343 7-3v-6"
                      />
                    </svg>

                    {{ this.translate.placeholderBddSearch }}</a
                  >
                </li>
              </ul>
            </div>

            <button class="btn btn-primary btn-md">
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                ></path>
              </svg>
              Trier
            </button>
          </div>
        </div>

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
              <i class="bi bi-filter-circle"></i> <i class="bi" :class="this.filterIcon"></i> filter
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
              <i class="bi bi-database"></i> show query
            </div>
          </div>
        </div>
      </form>
    </div>

    <div v-if="this.loading">
      <SkeletonTable :rows="5" :columns="5" :full="true" />
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
        :current-page="cPage"
        :nb-elements="cLimit"
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

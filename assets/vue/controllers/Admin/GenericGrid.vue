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
import SqlHighLight from '@/vue/Components/Global/SqlHighlight.vue';

export default {
  name: 'GenericGrid',
  components: {
    SqlHighLight,
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
      sortOrders: { Id: -1 },
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
      showMoreOption: false,
      urlSaveQuery: '',
      filter: 'all',
      orderField: 'id',
      listOrderField: {},
      order: 'DESC',
      listOrder: { 0: 'ASC', 1: 'DESC' },
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
      let order = this.getOrderParams();

      let tmp = this.url.split('/');
      let url = this.url + '/' + page + '/' + limit + filter + strSearch + order;
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
          strSearch +
          order;
      }

      axios
        .get(url)
        .then((response) => {
          this.gridColumns = response.data.column;
          this.gridData = response.data.data;
          this.nbElements = response.data.nb;
          this.listLimit = response.data.listLimit;
          this.translate = response.data.translate.genericGrid;
          this.translateGridPaginate = response.data.translate.gridPaginate;
          this.translateGrid = response.data.translate.grid;
          this.cPage = page;
          this.cLimit = limit;
          this.urlSaveQuery = response.data.urlSaveSql;

          if (this.searchPlaceholder === '') {
            this.searchPlaceholder = this.translate.placeholder;
          }

          if (response.data.listOrderField !== undefined) {
            this.listOrderField = response.data.listOrderField;
            for (var key in this.listOrderField) {
              this.sortOrders[this.listOrderField[key]] = 1;
            }
          }

          if (this.order === 'DESC') {
            this.sortOrders[this.listOrderField[this.orderField]] = -1;
          }

          if (response.data.sql !== undefined) {
            this.cQuery = response.data.sql;
          }

          console.log(this.sortOrders);
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
     * Génération du paramètre de trie
     * @returns {string}
     */
    getOrderParams() {
      return '&orderField=' + this.orderField + '&order=' + this.order;
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
      this.btnSearchMode.hide();
      this.showQuery = bool;
    },

    /**
     * Affiche le bloc plus d'options
     * @param bool
     */
    showMoreOptionBloc(bool) {
      this.btnSearchMode.hide();
      this.showMoreOption = bool;
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

      this.btnSearchMode.hide();

      this.loadData(1, this.limit);
    },

    /**
     * Change l'ordre et le trie
     */
    changeOrder() {
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

            <button
              id="searchModeButton"
              type="button"
              data-dropdown-open="searchMode"
              data-dropdown-offset-skidding="100"
              data-dropdown-placement="left"
              class="btn btn-outline-primary btn-md btn-icon"
            >
              <svg
                class="icon-sm"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 13v-2a1 1 0 0 0-1-1h-.757l-.707-1.707.535-.536a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0l-.536.535L14 4.757V4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v.757l-1.707.707-.536-.535a1 1 0 0 0-1.414 0L4.929 6.343a1 1 0 0 0 0 1.414l.536.536L4.757 10H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h.757l.707 1.707-.535.536a1 1 0 0 0 0 1.414l1.414 1.414a1 1 0 0 0 1.414 0l.536-.535 1.707.707V20a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-.757l1.707-.708.536.536a1 1 0 0 0 1.414 0l1.414-1.414a1 1 0 0 0 0-1.414l-.535-.536.707-1.707H20a1 1 0 0 0 1-1Z"
                />
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                />
              </svg>
            </button>

            <div
              id="searchMode"
              class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-auto border-1 border-gray-200 text-gray-700"
            >
              <div class="px-4 py-2 text-sm flex items-center">
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
                    stroke-width="2"
                    d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                  />
                </svg>
                <div class="font-medium">{{ this.translate.titleSearch }}</div>
              </div>
              <ul class="py-2 text-sm" aria-labelledby="dropdownDefaultButton">
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

                    {{ this.translate.textTableSearch }}</a
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

                    {{ this.translate.textBddSearch }}</a
                  >
                </li>
              </ul>
              <div class="py-2">
                <a
                  href="#"
                  class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                  @click="this.showQuery ? this.showQueryRun(false) : this.showQueryRun(true)"
                >
                  <svg
                    class="w-5 h-5 mr-3 text-[var(--primary)]"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
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

                  <span class="no-control" v-if="!this.showQuery">{{ this.translate.textShowQuery }}</span>
                  <span class="no-control" v-else>{{ this.translate.textHideQuery }}</span>
                </a>
              </div>
              <ul v-if="this.showFilter" class="py-2 text-sm">
                <li>
                  <a
                    class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                    href="#"
                    @click="this.changeFilter('me')"
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
                        d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                      />
                    </svg>
                    {{ this.translate.filterOnlyMe }}</a
                  >
                </li>
                <li>
                  <a
                    class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                    href="#"
                    @click="this.changeFilter('all')"
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
                        stroke-width="2"
                        d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                      />
                    </svg>

                    {{ this.translate.filterAll }}</a
                  >
                </li>
              </ul>
              <div class="py-2">
                <a
                  href="#"
                  class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                  @click="this.showMoreOption ? this.showMoreOptionBloc(false) : this.showMoreOptionBloc(true)"
                >
                  <svg
                    class="w-5 h-5 mr-3 text-[var(--primary)]"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 13v-2a1 1 0 0 0-1-1h-.757l-.707-1.707.535-.536a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0l-.536.535L14 4.757V4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v.757l-1.707.707-.536-.535a1 1 0 0 0-1.414 0L4.929 6.343a1 1 0 0 0 0 1.414l.536.536L4.757 10H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h.757l.707 1.707-.535.536a1 1 0 0 0 0 1.414l1.414 1.414a1 1 0 0 0 1.414 0l.536-.535 1.707.707V20a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-.757l1.707-.708.536.536a1 1 0 0 0 1.414 0l1.414-1.414a1 1 0 0 0 0-1.414l-.535-.536.707-1.707H20a1 1 0 0 0 1-1Z"
                    />
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                    />
                  </svg>
                  <span class="no-control" v-if="!this.showMoreOption">{{ this.translate.textShowTrieOption }}</span>
                  <span class="no-control" v-else>{{ this.translate.textHideTrieOption }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <SqlHighLight
          v-if="this.showQuery"
          :sql="this.cQuery"
          :label="this.translate.queryTitle"
          @copy-sql="this.copyQueryRun"
          @hide-sql="this.showQueryRun(false)"
          @save-sql="this.saveQueryRun"
        >
        </SqlHighLight>

        <div
          v-if="this.showMoreOption"
          class="bg-[var(--bg-card)] rounded-xl shadow-sm border border-[var(--border-color)] mt-3"
        >
          <div class="flex items-center justify-between px-4 py-3 bg-[var(--bg-main)] rounded-xl">
            <span class="text-sm text-slate-400 flex items-center">
              <svg
                class="h-4 w-4 me-2"
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
                  d="M21 13v-2a1 1 0 0 0-1-1h-.757l-.707-1.707.535-.536a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0l-.536.535L14 4.757V4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v.757l-1.707.707-.536-.535a1 1 0 0 0-1.414 0L4.929 6.343a1 1 0 0 0 0 1.414l.536.536L4.757 10H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h.757l.707 1.707-.535.536a1 1 0 0 0 0 1.414l1.414 1.414a1 1 0 0 0 1.414 0l.536-.535 1.707.707V20a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-.757l1.707-.708.536.536a1 1 0 0 0 1.414 0l1.414-1.414a1 1 0 0 0 0-1.414l-.535-.536.707-1.707H20a1 1 0 0 0 1-1Z"
                />
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                />
              </svg>
              {{ this.translate.titleTrieOption }}
            </span>
            <div>
              <button @click="this.showMoreOptionBloc(false)" class="btn-icon btn btn-ghost-primary">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                  ></path>
                </svg>
              </button>
            </div>
          </div>
          <div class="p-5">
            <div>
              <h3 class="text-[var(--primary)] font-bold">{{ this.translate.titleTrieOptionSubMenu }}</h3>
              <div class="flex justify-items-center">
                <div class="form-group me-3">
                  <label class="form-label">{{ this.translate.trieOptionListeField }}</label>
                  <select class="form-input" v-model="this.orderField">
                    <option v-for="(label, field) in this.listOrderField" :value="field">{{ label }}</option>
                  </select>
                </div>

                <div class="form-group me-3">
                  <label class="form-label">{{ this.translate.trieOptionListeOrder }}</label>
                  <select class="form-input" v-model="this.order">
                    <option v-for="order in this.listOrder" :value="order">{{ order }}</option>
                  </select>
                </div>

                <div class="mt-[2rem]">
                  <button type="button" class="btn btn-primary btn-sm" @click="this.changeOrder">
                    {{ this.translate.trieOptionBtn }}
                  </button>
                </div>
              </div>
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

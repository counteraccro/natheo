<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.6
 * Permet de générer le tableau GRID
 */

import { defineComponent } from 'vue';
import axios from 'axios';
import { Dropdown } from 'flowbite';
import Grid from '@/vue/Components/Grid/Grid.vue';
import GridPaginate from '@/vue/Components/Grid/GridPaginate.vue';
import Modal from '@/vue/Components/Global/Modal.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import SqlHighLight from '@/vue/Components/Global/SqlHighlight.vue';
import { copyToClipboard } from '@/utils/copyToClipboard';
import type { GridRow, GridSearchMode, GridSortOrders, GridTranslate } from '@/ts/Grid/Grid.type';
import type { GridPaginateTranslate } from '@/ts/Grid/GridPaginate.type';
import type {
  GenericGridActionResponse,
  GenericGridFilter,
  GenericGridHttpType,
  GenericGridOrder,
  GenericGridResponse,
  GenericGridTranslate,
} from '@/ts/Grid/GenericGrid.type';
import type { Toasts } from '@/ts/Toast/Toast.type';

export default defineComponent({
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
    url: { type: String, required: true },
    page: { type: Number, default: 1 },
    limit: { type: [String, Number], required: true },
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
      searchQuery: '' as string,
      gridColumns: [] as string[],
      gridData: [] as GridRow[],
      sortOrders: { Id: -1 } as GridSortOrders,
      nbElements: 0 as number,
      loading: true as boolean,
      cPage: this.page as number,
      cLimit: this.limit as string | number,
      cUrl: '' as string,
      isAjax: false as boolean,
      httpType: undefined as GenericGridHttpType | undefined,
      csrfToken: undefined as string | undefined,
      listLimit: {} as Record<string, number>,
      translate: {} as GenericGridTranslate,
      translateGridPaginate: {} as GridPaginateTranslate,
      translateGrid: {} as GridTranslate,
      showModalGenericGrid: false as boolean,
      msgConfirm: '' as string,
      searchMode: 'table' as GridSearchMode,
      searchPlaceholder: '' as string,
      cQuery: '' as string,
      showQuery: false as boolean,
      showMoreOption: false as boolean,
      urlSaveQuery: '' as string,
      filter: 'all' as GenericGridFilter,
      orderField: 'id' as string,
      listOrderField: {} as Record<string, string>,
      order: 'DESC' as GenericGridOrder,
      listOrder: { 0: 'ASC', 1: 'DESC' } as Record<number, GenericGridOrder>,
      btnSearchMode: null as Dropdown | null,
      toasts: {
        toastSuccessGenericGrid: {
          show: false,
          msg: '',
        },
        toastErrorGenericGrid: {
          show: false,
          msg: '',
        },
      } as Toasts,
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
    loadData(page: number, limit: string | number): void {
      this.loading = true;

      const strSearch = this.getSearchParams();
      const filter = this.getFilterParams();
      const order = this.getOrderParams();

      const tmp = this.url.split('/');
      let url = this.url + '/' + page + '/' + limit + filter + strSearch + order;
      // Url contenant déjà page/limit : on les remplace
      if (tmp.length > 6) {
        url = tmp.slice(0, 6).join('/') + '/' + page + '/' + limit + filter + strSearch + order;
      }

      axios
        .get<GenericGridResponse>(url)
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
          this.urlSaveQuery = response.data.urlSaveSql ?? '';

          if (this.searchPlaceholder === '') {
            this.searchPlaceholder = this.translate.placeholder;
          }

          if (response.data.listOrderField !== undefined) {
            this.listOrderField = response.data.listOrderField;
            for (const key in this.listOrderField) {
              this.sortOrders[this.listOrderField[key]] = 1;
            }
          }

          if (this.order === 'DESC') {
            this.sortOrders[this.listOrderField[this.orderField]] = -1;
          }

          if (response.data.sql !== undefined) {
            this.cQuery = response.data.sql;
          }
        })
        .catch((error: unknown) => {
          console.error(error);
        })
        .finally(() => (this.loading = false));
    },

    /**
     * Génération du paramètre filtre
     */
    getFilterParams(): string {
      return '?filter=' + this.filter;
    },

    /**
     * Génération du paramètre de recherche
     */
    getSearchParams(): string {
      if (this.searchMode !== 'table') {
        return '&search=' + encodeURIComponent(this.searchQuery);
      }
      return '';
    },

    /**
     * Génération du paramètre de trie
     */
    getOrderParams(): string {
      return '&orderField=' + this.orderField + '&order=' + this.order;
    },

    /**
     * Rechargement de la page
     */
    reloadData(): void {
      this.loadData(this.page, this.cLimit);
    },

    /**
     * Défini l'action à faire en fonction des paramètres
     * @param url
     * @param is_ajax
     * @param is_confirm
     * @param msg_confirm
     * @param type
     * @param csrf jeton CSRF optionnel, envoyé dans le header X-CSRF-TOKEN
     */
    redirectAction(
      url: string,
      is_ajax: boolean,
      is_confirm?: boolean,
      msg_confirm?: string,
      type?: GenericGridHttpType,
      csrf?: string
    ): void {
      this.cUrl = url;
      this.isAjax = is_ajax;
      this.httpType = type;
      this.csrfToken = csrf;
      this.msgConfirm = this.translate.confirmText;
      this.hideModal();

      if (is_confirm) {
        this.msgConfirm = msg_confirm ?? '';
        this.showModal();
        return;
      }

      if (!is_ajax) {
        window.location.href = url;
        return;
      }

      this.loading = true;

      if (type === undefined) {
        type = 'post';
        console.error('URL ' + url + " n'a aucun type défini");
      }

      const config = csrf ? { headers: { 'X-CSRF-TOKEN': csrf } } : {};
      const request =
        type === 'get' || type === 'delete'
          ? axios[type]<GenericGridActionResponse>(url, config)
          : axios[type]<GenericGridActionResponse>(url, undefined, config);

      request
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

            this.showToast('toastSuccessGenericGrid', response.data.msg);
          } else {
            this.showToast('toastErrorGenericGrid', response.data.msg);
          }
        })
        .catch((error: unknown) => {
          const msg = axios.isAxiosError<GenericGridActionResponse>(error) ? error.response?.data?.msg : undefined;
          if (msg) {
            this.showToast('toastErrorGenericGrid', msg);
          }
          console.error(error);
        })
        .finally(() => this.loadData(this.cPage, this.cLimit));
    },

    /**
     * Défini le trie à faire
     * @param field
     * @param order
     */
    sortAction(field: string, order: number): void {
      for (const key in this.listOrderField) {
        if (this.listOrderField[key] === field) {
          this.orderField = key;
        }
      }
      this.order = order === -1 ? 'DESC' : 'ASC';
      this.loadData(this.page, this.limit);
    },

    /**
     * Affichage la modale
     */
    showModal(): void {
      this.showModalGenericGrid = true;
    },

    /**
     * Ferme la modale
     */
    hideModal(): void {
      this.showModalGenericGrid = false;
    },

    /**
     * Affiche le toast défini par nameToast avec le message msg
     * @param nameToast
     * @param msg
     */
    showToast(nameToast: string, msg: string): void {
      this.toasts[nameToast].msg = msg;
      this.toasts[nameToast].show = true;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },

    /**
     * Permet de changer de mode de recherche
     * @param mode
     */
    changeSearchMode(mode: GridSearchMode): boolean {
      this.searchMode = mode;
      if (this.searchMode === 'table') {
        this.searchPlaceholder = this.translate.placeholder;
      } else {
        this.searchPlaceholder = this.translate.placeholderBddSearch;
      }
      this.btnSearchMode?.hide();

      return false;
    },

    /**
     * Affiche la requete SQL
     * @param bool
     */
    showQueryRun(bool: boolean): void {
      this.btnSearchMode?.hide();
      this.showQuery = bool;
    },

    /**
     * Affiche le bloc plus d'options
     * @param bool
     */
    showMoreOptionBloc(bool: boolean): void {
      this.btnSearchMode?.hide();
      this.showMoreOption = bool;
    },

    /**
     * Fait un copier coller
     */
    async copyQueryRun(): Promise<void> {
      try {
        await copyToClipboard(this.cQuery);
        this.showToast('toastSuccessGenericGrid', this.translate.copySuccess);
      } catch {
        this.showToast('toastErrorGenericGrid', this.translate.copyError);
      }
    },

    saveQueryRun(): void {
      this.loading = true;
      axios
        .post<GenericGridActionResponse>(this.urlSaveQuery, {
          query: this.cQuery,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.showToast('toastSuccessGenericGrid', response.data.msg);
          } else {
            this.showToast('toastErrorGenericGrid', response.data.msg);
          }
        })
        .catch((error: unknown) => {
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
    changeFilter(filterChange: GenericGridFilter): void {
      this.filter = filterChange;
      this.btnSearchMode?.hide();

      this.loadData(1, this.limit);
    },

    /**
     * Change l'ordre et le trie
     */
    changeOrder(): void {
      this.loadData(1, this.limit);
    },
  },
});
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
                class="form-input input-icon-left no-control"
                :placeholder="searchPlaceholder"
                v-model="searchQuery"
              />
            </div>
          </div>

          <div class="flex gap-2">
            <button
              :disabled="!activeSearchData"
              v-if="searchMode === 'bdd'"
              type="button"
              @click="loadData(cPage, cLimit)"
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

              {{ translate.btnSearch }}
            </button>

            <button
              v-if="showFilter"
              class="btn btn-outline-primary btn-md btn-icon"
              :title="filter === 'me' ? translate.filterOnlyMe : translate.filterAll"
            >
              <svg
                v-if="filter === 'me'"
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
                  stroke-width="2"
                  d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                />
              </svg>
              <svg
                v-else
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
                  d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                />
              </svg>
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
                <div class="font-medium">{{ translate.titleSearch }}</div>
              </div>
              <ul class="py-2 text-sm" aria-labelledby="dropdownDefaultButton">
                <li>
                  <a
                    href="#"
                    class="no-control px-4 py-2 hover:bg-gray-100 flex item-center"
                    @click="changeSearchMode('table')"
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

                    {{ translate.textTableSearch }}</a
                  >
                </li>
                <li>
                  <a
                    href="#"
                    class="no-control flex items-center px-4 py-2 hover:bg-gray-100"
                    @click="changeSearchMode('bdd')"
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

                    {{ translate.textBddSearch }}</a
                  >
                </li>
              </ul>
              <div class="py-2">
                <a
                  href="#"
                  class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                  @click="showQuery ? showQueryRun(false) : showQueryRun(true)"
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

                  <span class="no-control" v-if="!showQuery">{{ translate.textShowQuery }}</span>
                  <span class="no-control" v-else>{{ translate.textHideQuery }}</span>
                </a>
              </div>
              <ul v-if="showFilter" class="py-2 text-sm">
                <li>
                  <a
                    class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                    href="#"
                    @click="changeFilter('me')"
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
                    {{ translate.filterOnlyMe }}</a
                  >
                </li>
                <li>
                  <a
                    class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                    href="#"
                    @click="changeFilter('all')"
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

                    {{ translate.filterAll }}</a
                  >
                </li>
              </ul>
              <div class="py-2">
                <a
                  href="#"
                  class="no-control flex items-center px-4 py-2 text-sm hover:bg-gray-100"
                  @click="showMoreOption ? showMoreOptionBloc(false) : showMoreOptionBloc(true)"
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
                  <span class="no-control" v-if="!showMoreOption">{{ translate.textShowTrieOption }}</span>
                  <span class="no-control" v-else>{{ translate.textHideTrieOption }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <SqlHighLight
          v-if="showQuery"
          :sql="cQuery"
          :label="translate.queryTitle"
          @copy-sql="copyQueryRun"
          @hide-sql="showQueryRun(false)"
          @save-sql="saveQueryRun"
        >
        </SqlHighLight>

        <div
          v-if="showMoreOption"
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
              {{ translate.titleTrieOption }}
            </span>
            <div>
              <button @click="showMoreOptionBloc(false)" class="btn-icon btn btn-ghost-primary">
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
              <h3 class="text-[var(--primary)] font-bold">{{ translate.titleTrieOptionSubMenu }}</h3>
              <div class="flex justify-items-center">
                <div class="form-group me-3">
                  <label class="form-label">{{ translate.trieOptionListeField }}</label>
                  <select class="form-input" v-model="orderField">
                    <option v-for="(label, field) in listOrderField" :value="field">{{ label }}</option>
                  </select>
                </div>

                <div class="form-group me-3">
                  <label class="form-label">{{ translate.trieOptionListeOrder }}</label>
                  <select class="form-input" v-model="order">
                    <option v-for="order in listOrder" :value="order">{{ order }}</option>
                  </select>
                </div>

                <div class="mt-[2rem]">
                  <button type="button" class="btn btn-primary btn-sm" @click="changeOrder">
                    {{ translate.trieOptionBtn }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div v-if="loading">
      <SkeletonTable :rows="5" :columns="5" :full="true" />
    </div>
    <div v-else>
      <Grid
        :data="gridData"
        :columns="gridColumns"
        :filter-key="searchQuery"
        :sortOrders="sortOrders"
        :translate="translateGrid"
        :search-mode="searchMode"
        @redirect-action="redirectAction"
        @sort-action="sortAction"
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
    :show="showModalGenericGrid"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #icon>
      <svg class="h-6 w-6 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
    </template>
    <template #title> {{ translate.confirmTitle }} </template>
    <template #body>
      <div v-html="msgConfirm"></div>
    </template>
    <template #footer>
      <button
        type="button"
        class="btn btn-primary btn-sm me-2"
        @click="redirectAction(cUrl, isAjax, false, '', httpType, csrfToken)"
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
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
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
      :show="toasts.toastSuccessGenericGrid.show"
      @close-toast="closeToast"
    >
      <template #body>
        <div v-html="toasts.toastSuccessGenericGrid.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastErrorGenericGrid'"
      :type="'danger'"
      :show="toasts.toastErrorGenericGrid.show"
      @close-toast="closeToast"
    >
      <template #body>
        <div v-html="toasts.toastErrorGenericGrid.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style scoped></style>

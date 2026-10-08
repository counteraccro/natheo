<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Permet d'afficher les logs sous forme de tableau d'après les fichiers de logs
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import Grid from '@/vue/Components/Grid/Grid.vue';
import GridPaginate from '@/vue/Components/Grid/GridPaginate.vue';
import Modal from '@/vue/Components/Global/Modal.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import type { Toasts } from '@/ts/Toast/Toast.type';
import type { GridRow, GridSortOrders, GridTranslate } from '@/ts/Grid/Grid.type';
import type { GenericGridTranslate } from '@/ts/Grid/GenericGrid.type';
import type { GridPaginateTranslate } from '@/ts/Grid/GridPaginate.type';
import type {
  LogActionResponse,
  LogFile,
  LogFilesResponse,
  LogLoadFileResponse,
  LogTimeFilter,
  LogTranslate,
} from '@/ts/Log/Log.type';

export default defineComponent({
  name: 'Log',
  components: { SkeletonTable, SkeletonText, Toast, Modal, GridPaginate, Grid },
  props: {
    url_select: { type: String, required: true },
    url_load_log_file: { type: String, required: true },
    url_delete_file: { type: String, required: true },
    url_download_file: { type: String, required: true },
    csrf_delete_file: { type: String, required: true },
    translate: { type: Object as PropType<LogTranslate>, required: true },
    page: { type: Number, required: true },
    limit: { type: Number, required: true },
  },
  data() {
    return {
      files: [] as LogFile[],
      time: 'now' as LogTimeFilter,
      searchQuery: '' as string,
      gridColumns: [] as string[],
      gridData: [] as GridRow[],
      sortOrders: {} as GridSortOrders,
      nbElements: 0 as number,
      loading: true as boolean,
      cPage: this.page as number,
      cLimit: this.limit as number,
      listLimit: {} as Record<string, number>,
      translateGenericGrid: {} as Partial<GenericGridTranslate>,
      translateGridPaginate: {} as GridPaginateTranslate,
      translateGrid: {} as GridTranslate,
      selectFile: '' as string,
      taille: '0 Ko' as string,
      modalDeleteLog: false as boolean,
      toasts: {
        toastSuccess: { show: false, msg: '' },
        toastError: { show: false, msg: '' },
      } as Toasts,
    };
  },
  computed: {
    /**
     * Chemin du fichier sélectionné encodé segment par segment, les "/" séparent les dossiers
     */
    encodedSelectFile(): string {
      return this.selectFile.split('/').map(encodeURIComponent).join('/');
    },
  },
  mounted() {
    this.loadData();
  },
  methods: {
    /**
     * Charge la liste des fichiers de logs en fonction de la temporalité
     */
    loadData(): void {
      this.loading = true;
      axios
        .get<LogFilesResponse>(this.url_select + '/' + this.time)
        .then((response) => {
          this.files = response.data.files;
        })
        .catch((error) => {
          this.showRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Event de changement de temporalité
     */
    changeTimeFilter(): void {
      this.selectFile = '';
      this.loadData();
    },

    /**
     * Event de choix du fichier de log
     */
    selectLogFile(): void {
      if (this.selectFile !== '') {
        this.loadContentFile(1, this.limit);
      }
    },

    /**
     * Charge le contenu d'un log
     * @param page
     * @param limit
     */
    loadContentFile(page: number, limit: number | string): void {
      this.loading = true;
      axios
        .get<LogLoadFileResponse>(this.url_load_log_file + '/' + page + '/' + limit + '/' + this.encodedSelectFile)
        .then((response) => {
          if (page === 1) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          }

          const grid = response.data.grid;
          this.gridColumns = grid.column;
          this.gridData = grid.data;
          this.nbElements = grid.nb;
          this.sortOrders = Object.fromEntries(grid.column.map((key) => [key, 1]));
          this.listLimit = grid.listLimit;
          this.translateGenericGrid = grid.translate.genericGrid;
          this.translateGridPaginate = grid.translate.gridPaginate;
          this.translateGrid = grid.translate.grid;
          this.cPage = page;
          this.cLimit = Number(limit);
          this.taille = grid.taille;
        })
        .catch((error) => {
          this.showRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Affiche la modale de confirmation de suppression
     */
    confirmDelete(): void {
      this.modalDeleteLog = true;
    },

    /**
     * Supprime le fichier sélectionné
     */
    deleteFile(): void {
      this.hideModal();
      this.loading = true;

      axios
        .delete<LogActionResponse>(this.url_delete_file + '/' + this.encodedSelectFile, {
          headers: { 'X-CSRF-TOKEN': this.csrf_delete_file },
        })
        .then((response) => {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
          this.time = 'now';
          this.selectFile = '';
          this.taille = '0 Ko';
          this.nbElements = 0;
          this.loadData();
        })
        .catch((error) => {
          this.showRequestError(error);
          this.loading = false;
        });
    },

    /**
     * Lance le téléchargement, la réponse étant en "attachment" la page courante n'est pas quittée
     */
    download(): void {
      window.location.href = this.url_download_file + '/' + this.encodedSelectFile;
    },

    /**
     * Ferme la modale
     */
    hideModal(): void {
      this.modalDeleteLog = false;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },

    /**
     * Affiche le message d'erreur renvoyé par le serveur ou un message générique
     * @param error
     */
    showRequestError(error: unknown): void {
      console.error(error);
      const data = axios.isAxiosError<LogActionResponse>(error) ? error.response?.data : undefined;
      this.toasts.toastError.msg = data?.msg ?? this.translate.log_error_request;
      this.toasts.toastError.show = true;
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div v-if="loading">
      <SkeletonText :nb-paragraphe="2" />
    </div>

    <div class="card-header">
      <div>
        <div class="card-title">
          <svg
            class="card-icon"
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
              d="M10 3v4a1 1 0 0 1-1 1H5m8 7.5 2.5 2.5M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Zm-5 9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"
            />
          </svg>

          {{ translate.log_block_search_title }}
        </div>
        <p class="card-subtitle">
          {{ translate.log_block_search_sub_title }}
        </p>
      </div>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
        <div class="form-group">
          <label class="form-label" for="select-time">{{ translate.log_select_time_label }}</label>
          <select class="form-input no-control" id="select-time" v-model="time" @change="changeTimeFilter">
            <option value="all">{{ translate.log_select_time_all }}</option>
            <option value="now">{{ translate.log_select_time_now }}</option>
            <option value="yesterday">{{ translate.log_select_time_yesterday }}</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="select-file">{{ translate.log_select_file_label }}</label>
          <select class="form-input no-control" id="select-file" v-model="selectFile" @change="selectLogFile">
            <option value="">{{ translate.log_select_file }}</option>
            <option v-for="option in files" :key="option.path" :value="option.path">{{ option.name }}</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div v-if="loading">
      <SkeletonTable :full="true" />
    </div>

    <div v-else>
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
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
                d="M10 3v4a1 1 0 0 1-1 1H5m4 8h6m-6-4h6m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"
              />
            </svg>

            {{ translate.log_file }}
            <template v-if="selectFile !== ''">
              {{ selectFile }}
            </template>
            <template v-else> ---- </template>
          </div>

          <p class="card-subtitle">
            {{ translate.log_file_size }} {{ taille }} - {{ nbElements }} {{ translate.log_file_ligne }}
          </p>
        </div>

        <div class="card-actions">
          <button
            :disabled="selectFile === ''"
            :title="translate.log_btn_reload"
            class="btn btn-sm btn-primary btn-icon me-2"
            @click="loadContentFile(1, cLimit)"
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
                d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"
              />
            </svg>
          </button>

          <button
            :disabled="selectFile === ''"
            :title="translate.log_btn_download_file"
            class="btn btn-sm btn-primary btn-icon me-2"
            @click="download"
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
                d="M12 13V4M7 14H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2m-1-5-4 5-4-5m9 8h.01"
              />
            </svg>
          </button>

          <button
            :disabled="selectFile === ''"
            :title="translate.log_btn_delete_file"
            class="btn btn-sm btn-dark btn-icon"
            @click="confirmDelete"
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
                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"
              />
            </svg>
          </button>
        </div>
      </div>

      <div v-if="selectFile === ''">
        <p class="text-center text-[var(--text-secondary)] text-sm italic flex justify-center gap-1 p-4 pt-0">
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
              d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>
          {{ translate.log_empty_file }}
        </p>
      </div>

      <div v-else class="p-5">
        <div class="input-group mb-4">
          <svg class="icon icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            ></path>
          </svg>

          <input
            type="text"
            class="form-input input-icon-left no-control"
            :placeholder="translateGenericGrid.placeholder"
            v-model="searchQuery"
          />
        </div>

        <Grid
          :data="gridData"
          :columns="gridColumns"
          :filter-key="searchQuery"
          :sort-orders="sortOrders"
          :translate="translateGrid"
          :search-mode="'table'"
        >
        </Grid>

        <GridPaginate
          :current-page="cPage"
          :nb-elements="cLimit"
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

  <!-- modale confirmation suppression -->
  <modal :id="'modalDeleteLog'" :show="modalDeleteLog" @close-modal="hideModal" :option-show-close-btn="false">
    <template #title> {{ translateGenericGrid.confirmTitle }} </template>
    <template #body>
      <div>
        {{ translate.log_delete_file_confirm }} <b>{{ selectFile }}</b> ? <br />
        {{ translate.log_delete_file_confirm_2 }}
      </div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="deleteFile">
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
        {{ translate.log_btn_delete_ok }}
      </button>

      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal">
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

        {{ translate.log_btn_delete_ko }}
      </button>
    </template>
  </modal>
  <!-- fin modale confirmation supression -->

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :show="toasts.toastSuccess.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.toastError.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

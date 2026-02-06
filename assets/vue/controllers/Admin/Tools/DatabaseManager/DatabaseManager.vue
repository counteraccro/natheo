<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Gestionnaire de base de données
 */
import axios from 'axios';
import Toast from '../../../../Components/Global/Toast.vue';
import Modal from '../../../../Components/Global/Modal.vue';
import SchemaDatabase from '../../../../Components/DatabaseManager/SchemaDatabse.vue';
import SchemaTable from '../../../../Components/DatabaseManager/SchemaTable.vue';
import ListDump from '../../../../Components/DatabaseManager/ListDump.vue';
import SkeletonCardStat from '@/vue/Components/Skeleton/CardStat.vue';
import SkeletonTabs from '@/vue/Components/Skeleton/Tabs.vue';

export default {
  name: 'DatabaseManager',
  components: {
    SkeletonTabs,
    SkeletonCardStat,
    ListDump,
    SchemaTable,
    SchemaDatabase,
    Modal,
    Toast,
  },
  props: {
    urls: Object,
    translate: Object,
  },
  data() {
    return {
      loading: true,
      result: {},
      tables: Object,
      disabledListeTales: true,
      schemaTable: {},
      schemaTableName: '',
      listDump: {},
      show: '',
      optionData: {
        filename: '',
        all: 1,
        tables: [],
        data: 'table',
      },
      modalTab: {
        modaleDumpOption: false,
      },
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
      },
    };
  },
  mounted() {
    let now = new Date();
    this.optionData.filename =
      'dump-bdd-' +
      now.getDate() +
      '-' +
      now.getMonth() +
      '-' +
      now.getFullYear() +
      '-' +
      now.getHours() +
      '-' +
      now.getMinutes() +
      '-' +
      now.getSeconds();
    this.loadSchemaDataBase();
    this.loadListeDump();
    this.loadDataDump();
  },
  methods: {
    /**
     * Chargement du schema de la base de donnée
     */
    loadSchemaDataBase() {
      this.loading = true;
      axios
        .get(this.urls.load_schema_database)
        .then((response) => {
          this.result = response.data.query;
          this.result.header['action'] = this.translate.action;
          console.log(this.result);
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Charge le schema de la table
     * @param table
     */
    loadSchemaTable(table) {
      this.schemaTable = Object;
      this.schemaTableName = '';
      this.loading = true;
      axios
        .get(this.urls.load_schema_table + '/' + table)
        .then((response) => {
          this.schemaTable = response.data.result;
          this.schemaTableName = response.data.result.table;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Charge les listes des sauvegardes, faites
     */
    loadListeDump() {
      this.loading = true;
      axios
        .get(this.urls.all_dump_file)
        .then((response) => {
          this.listDump = response.data.result;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Chargement des données pour le dump SQL
     */
    loadDataDump() {
      this.loading = true;
      axios
        .get(this.urls.load_tables_database)
        .then((response) => {
          this.tables = response.data.tables;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Créer une nouvelle sauvegarde
     */
    dumpSQL() {
      this.loading = true;
      axios
        .post(this.urls.save_database, {
          options: this.optionData,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.closeModal('modaleDumpOption');
        });
    },

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModale(nameModale, state) {
      this.modalTab[nameModale] = state;
    },

    /**
     * Ferme une modale
     * @param nameModale
     */
    closeModal(nameModale) {
      this.updateModale(nameModale, false);
    },
  },
};
</script>

<template>
  <div v-if="loading">
    <skeleton-card-stat />
    <skeleton-tabs />
  </div>

  <div v-else>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        class="card rounded-lg p-6 text-white"
        style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-3xl font-bold">{{ result.stat.nbTable }}</p>
            <p class="text-sm font-medium mt-1">{{ translate.stat_nb_table }}</p>
          </div>
          <div class="p-3 rounded-lg bg-[var(--primary)] opacity-60">
            <svg
              class="w-8 h-8 text-white opacity-100"
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
          </div>
        </div>
      </div>
      <div
        class="card rounded-lg p-6 text-white"
        style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-3xl font-bold">{{ result.stat.nbElement }}</p>
            <p class="text-sm font-medium mt-1">{{ translate.stat_nb_row }}</p>
          </div>
          <div class="p-3 rounded-lg bg-[var(--primary)] opacity-60">
            <svg
              class="w-8 h-8 text-white opacity-100"
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
          </div>
        </div>
      </div>
      <div
        class="card rounded-lg p-6 text-white"
        style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-3xl font-bold">{{ result.stat.sizeBite }}</p>
            <p class="text-sm font-medium mt-1">{{ translate.stat_size }}</p>
          </div>
          <div class="p-3 rounded-lg bg-[var(--primary)] opacity-60">
            <svg
              class="w-8 h-8 text-white opacity-100"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
            >
              <path
                stroke="currentColor"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card rounded-lg mb-6 mt-6">
    <div class="border-b border-default" style="border-color: var(--border-color)">
      <ul
        class="flex flex-wrap -mb-px text-sm font-medium text-center"
        id="default-styled-tab"
        data-tabs-toggle="#nav-tab-database-manager"
        data-tabs-active-classes="text-[var(--primary)] border-[var(--primary)]"
        role="tablist"
      >
        <li class="me-2" role="presentation">
          <button
            class="inline-block p-3 border-b-2 rounded-t-base text-[var(--primary)] border-[var(--primary)] cursor-pointer"
            id="nav-0-tab"
            data-tabs-target="#tab-0"
            type="button"
            role="tab"
            aria-controls="Site web"
            aria-selected="true"
          >
            <span class="flex items-center gap-2">
              <svg
                class="w-5 h-5"
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
              {{ translate.btn_schema_bdd }}
            </span>
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-block p-3 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
            :class="schemaTableName === '' ? 'opacity-40 cursor-not-allowed' : 'opacity-100 cursor-pointer'"
            id="nav-1-tab"
            data-tabs-target="#tab-1"
            type="button"
            role="tab"
            aria-controls="SEO"
            aria-selected="false"
            :disabled="schemaTableName === ''"
          >
            <span class="flex items-center gap-2">
              <svg
                class="w-5 h-5"
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
                  d="M3 11h18M3 15h18m-9-4v8m-8 0h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"
                />
              </svg>
              {{ translate.btn_schema_table + ' ' + schemaTableName }}
            </span>
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-block p-3 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300 cursor-pointer"
            id="nav-2-tab"
            data-tabs-target="#tab-2"
            type="button"
            role="tab"
            aria-controls="Administration"
            aria-selected="false"
          >
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              {{ translate.btn_generate_dump }}
            </span>
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-block p-3 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300 cursor-pointer"
            id="nav-3-tab"
            data-tabs-target="#tab-3"
            type="button"
            role="tab"
            aria-controls="Logs"
            aria-selected="false"
          >
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                ></path>
              </svg>
              {{ translate.btn_liste_dump }}
            </span>
          </button>
        </li>
      </ul>
    </div>

    <div id="nav-tab-database-manager">
      <div class="" id="tab-0" role="tabpanel" aria-labelledby="profile-tab">
        <SchemaDatabase
          :data="result"
          :table-name="schemaTableName"
          @load-schema-table="loadSchemaTable"
        ></SchemaDatabase>
      </div>
      <div class="hidden" id="tab-1" role="tabpanel" aria-labelledby="profile-tab">
        <SchemaTable :data="schemaTable" />
      </div>
      <div class="hidden" id="tab-2" role="tabpanel" aria-labelledby="profile-tab">
        <div class="max-w-2xl mx-auto mt-6 mb-6">
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2 text-[var(--text-primary)]">
              {{ translate.dump_option.title }}
            </h3>
            <p class="text-sm text-[var(--text-secondary)]">{{ translate.dump_option.sub_title_1 }}</p>
          </div>

          <div class="form-control">
            <label class="form-label">{{ translate.dump_option.filename_label }}</label>
            <input class="form-input" v-model="optionData.filename" />
            <span class="form-text">{{ translate.dump_option.filename_help }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 mt-6" style="color: var(--text-primary)">{{
              translate.dump_option.sub_title_2
            }}</label>
            <div
              class="space-y-2 p-4 rounded-lg border"
              style="border-color: var(--border-color); background-color: var(--bg-hover)"
            >
              <div class="form-check">
                <input
                  class="form-check-input"
                  style="color: var(--primary)"
                  type="radio"
                  value="1"
                  id="all-data"
                  v-model="optionData.all"
                  @click="disabledListeTales = true"
                />
                <label class="form-check-label" for="all-data">
                  {{ translate.dump_option.select_all }}
                </label>
              </div>
              <div class="form-check">
                <input
                  class="form-check-input"
                  style="color: var(--primary)"
                  type="radio"
                  value="0"
                  id="select-data"
                  v-model="optionData.all"
                  @click="disabledListeTales = false"
                />
                <label class="form-check-label" for="select-data">
                  {{ translate.dump_option.select_tables }}
                </label>
              </div>

              <div class="form-control">
                <select
                  id="select-multi-table"
                  class="form-input"
                  size="18"
                  :disabled="disabledListeTales"
                  multiple
                  v-model="optionData.tables"
                >
                  <option v-for="table in tables" :value="table.name">
                    {{ table.name }}
                  </option>
                </select>
              </div>

              <label class="flex items-center">
                <input type="checkbox" class="w-4 h-4 rounded" style="color: var(--primary)" />
                <span class="ml-2 text-sm" style="color: var(--text-primary)">Sélectionner manuellement</span>
              </label>
            </div>
          </div>
        </div>

        <!--
        <div class="row">
          <div class="col-6">
            <h5>{{ this.translate.modale_dump_option.sub_title_1 }}</h5>
            <div class="mb-2">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  value=""
                  id="flexCheckDefault"
                  v-model="this.optionData.all"
                  @click="
                    this.optionData.all === false ? (this.disabledListeTales = true) : (this.disabledListeTales = false)
                  "
                />
                <label class="form-check-label" for="flexCheckDefault">
                  {{ this.translate.modale_dump_option.select_all }}
                </label>
              </div>
            </div>
            <div class="mb-2">
              <label for="select-multi-table" class="form-label">{{
                this.translate.modale_dump_option.select_tables
              }}</label>
              <select
                id="select-multi-table"
                class="form-select"
                size="18"
                :disabled="this.disabledListeTales"
                multiple
                v-model="this.optionData.tables"
              >
                <option v-for="table in this.tables" :value="table.name">
                  {{ table.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-6">
            <h5>{{ this.translate.modale_dump_option.sub_title_2 }}</h5>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="option-dump-data"
                id="option-dump-data-1"
                value="table"
                v-model="this.optionData.data"
                checked
              />
              <label class="form-check-label" for="option-dump-data-1">
                {{ this.translate.modale_dump_option.option_table }}
              </label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="option-dump-data"
                id="option-dump-data-2"
                value="data"
                v-model="this.optionData.data"
              />
              <label class="form-check-label" for="option-dump-data-2">
                {{ this.translate.modale_dump_option.option_data }}
              </label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="option-dump-data"
                id="option-dump-data-3"
                value="table_data"
                v-model="this.optionData.data"
              />
              <label class="form-check-label" for="option-dump-data-3">
                {{ this.translate.modale_dump_option.option_table_data }}
              </label>
            </div>

            <div class="alert alert-secondary mt-2">
              <h6><i class="bi bi-info-circle-fill"></i> {{ this.translate.modale_dump_option.help_title }}</h6>
              <div v-html="this.translate.modale_dump_option.help_body"></div>
            </div>

            <div class="alert alert-danger mt-2">
              <h6>
                <i class="bi bi-exclamation-circle-fill"></i> {{ this.translate.modale_dump_option.warning_title }}
              </h6>
              <div v-html="this.translate.modale_dump_option.warning_body"></div>
            </div>
          </div>

          <div class="btn btn-secondary" @click="this.dumpSQL">
            {{ this.translate.modale_dump_option.btn_generate }}
          </div>
        </div>-->
      </div>
      <div class="hidden" id="tab-3" role="tabpanel" aria-labelledby="profile-tab">
        <ListDump :data="listDump" :translate="translate.list_dump" @refresh-dump="loadListeDump"></ListDump>
      </div>
    </div>
  </div>

  <!----   end
  <div id="block-sql-manager" :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div>
      <div class="btn btn-secondary me-2" @click="this.showSchemaDatabase">
        <i class="bi bi-table"></i> {{ this.translate.btn_schema_bdd }}
      </div>
      <div class="btn btn-secondary me-2" @click="this.openModalDumpSQL">
        <i class="bi bi-database-fill-down"></i> {{ this.translate.btn_generate_dump }}
      </div>
      <div class="btn btn-secondary" @click="this.loadListeDump">
        <i class="bi bi-filetype-sql"></i> {{ this.translate.btn_liste_dump }}
      </div>
      <div class="block-page">
        <SchemaDatabase
          v-if="this.show === 'schemaDatabase' && !this.result.length"
          :data="this.result"
          :translate="this.translate.schema_database"
          @load-schema-table="this.loadSchemaTable"
        ></SchemaDatabase>
        <div v-if="this.show === 'schemaTable'">
          <SchemaTable :data="this.schemaTable" :translate="this.translate.schema_table"> </SchemaTable>
        </div>
        <div v-if="this.show === 'dumps'">
          <ListDump :data="this.listDump" :translate="this.translate.list_dump"></ListDump>
        </div>
      </div>
    </div>

     modale confirmation suppression
    <modal
      :id="'modaleDumpOption'"
      :show="this.modalTab.modaleDumpOption"
      @close-modal="this.closeModal"
      :optionModalSize="'modal-lg'"
      :option-show-close-btn="false"
    >
      <template #title>
        <i class="bi bi-database-fill-down"></i>
        {{ this.translate.modale_dump_option.title }}
      </template>
      <template #body>
        <div class="row">
          <div class="col-6">
            <h5>{{ this.translate.modale_dump_option.sub_title_1 }}</h5>
            <div class="mb-2">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  value=""
                  id="flexCheckDefault"
                  v-model="this.optionData.all"
                  @click="
                    this.optionData.all === false ? (this.disabledListeTales = true) : (this.disabledListeTales = false)
                  "
                />
                <label class="form-check-label" for="flexCheckDefault">
                  {{ this.translate.modale_dump_option.select_all }}
                </label>
              </div>
            </div>
            <div class="mb-2">
              <label for="select-multi-table" class="form-label">{{
                this.translate.modale_dump_option.select_tables
              }}</label>
              <select
                id="select-multi-table"
                class="form-select"
                size="18"
                :disabled="this.disabledListeTales"
                multiple
                v-model="this.optionData.tables"
              >
                <option v-for="table in this.tables" :value="table.name">
                  {{ table.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-6">
            <h5>{{ this.translate.modale_dump_option.sub_title_2 }}</h5>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="option-dump-data"
                id="option-dump-data-1"
                value="table"
                v-model="this.optionData.data"
                checked
              />
              <label class="form-check-label" for="option-dump-data-1">
                {{ this.translate.modale_dump_option.option_table }}
              </label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="option-dump-data"
                id="option-dump-data-2"
                value="data"
                v-model="this.optionData.data"
              />
              <label class="form-check-label" for="option-dump-data-2">
                {{ this.translate.modale_dump_option.option_data }}
              </label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="option-dump-data"
                id="option-dump-data-3"
                value="table_data"
                v-model="this.optionData.data"
              />
              <label class="form-check-label" for="option-dump-data-3">
                {{ this.translate.modale_dump_option.option_table_data }}
              </label>
            </div>

            <div class="alert alert-secondary mt-2">
              <h6><i class="bi bi-info-circle-fill"></i> {{ this.translate.modale_dump_option.help_title }}</h6>
              <div v-html="this.translate.modale_dump_option.help_body"></div>
            </div>

            <div class="alert alert-danger mt-2">
              <h6>
                <i class="bi bi-exclamation-circle-fill"></i> {{ this.translate.modale_dump_option.warning_title }}
              </h6>
              <div v-html="this.translate.modale_dump_option.warning_body"></div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <div class="btn btn-secondary" @click="this.dumpSQL">{{ this.translate.modale_dump_option.btn_generate }}</div>
      </template>
    </modal>
    fin modale nouvelle categogie
  </div> -->

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="this.toasts.toastSuccess.show"
      @close-toast="this.closeToast"
    >
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
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

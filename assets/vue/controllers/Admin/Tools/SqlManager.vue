<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Gestionnaire de requete SQL
 */

import { defineComponent, PropType } from 'vue';
import axios from 'axios';
import Toast from '../../../Components/Global/Toast.vue';
import { emitter } from '@/utils/useEvent';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import SkeletonTabs from '@/vue/Components/Skeleton/Tabs.vue';
import SkeletonSearchResult from '@/vue/Components/Skeleton/SearchResult.vue';
import AlertPrimary from '@/vue/Components/Alert/Primary.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import type {
  SqlManagerQuery,
  DatabaseTable,
  SqlManagerUrls,
  SqlManagerTranslations,
  ToastState,
  LoadSqlManagerResponse,
  LoadDataDatabaseResponse,
  ExecuteSqlResponse,
  SaveResponse,
} from '@/ts/SqlManager/SqlManager.type';

export default defineComponent({
  name: 'SqlManager',
  components: { AlertDanger, AlertPrimary, SkeletonSearchResult, SkeletonTabs, SkeletonText, Toast },
  props: {
    urls: {
      type: Object as PropType<SqlManagerUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<SqlManagerTranslations>,
      required: true,
    },
    id: {
      type: Number,
      required: false,
    },
    isExecute: {
      type: Boolean,
      required: false,
      default: false,
    },
    schema: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      sqlManager: { id: null, name: null, query: null } as SqlManagerQuery,
      dataBaseData: [] as DatabaseTable[],
      selectTable: [] as string[],
      selectLabelTable: '',
      selectField: [] as string[],
      selectColumns: [] as string[],
      searchTable: '',
      searchField: '',
      result: [] as Record<string, string | number | null>[],
      resultHeader: [] as string[],
      error: '',
      isErrorValidateName: false,
      isErrorValidateQuery: false,
      showQueryBuilder: true,
      toasts: {
        toastSuccess: { show: false, msg: '' } as ToastState,
        toastError: { show: false, msg: '' } as ToastState,
      },
    };
  },
  computed: {
    /**
     * Filtre sur tables
     */
    filteredTable(): DatabaseTable[] {
      const search = this.searchTable.toLowerCase();
      if (!search) {
        return this.dataBaseData;
      }
      return this.dataBaseData.filter((table) => table.name.toLowerCase().includes(search));
    },

    /**
     * Filtre sur champs d'une table
     */
    filteredFieldName(): string[] {
      const search = this.searchField.toLowerCase();
      if (!search) {
        return this.selectColumns;
      }
      return this.selectColumns.filter((column) => column.toLowerCase().includes(search));
    },
  },
  mounted() {
    this.loadSqlManager();
    this.loadDataDatabase();
  },
  methods: {
    /**
     * Chargement des données SQLManager
     */
    loadSqlManager() {
      this.loading = true;
      axios
        .get<LoadSqlManagerResponse>(this.urls.load_sql_manager)
        .then((response) => {
          this.sqlManager = response.data.sqlManager;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;

          if (this.isExecute) {
            this.execute();
          }
        });
    },

    /**
     * Charge les informations de la base de données
     */
    loadDataDatabase() {
      this.loading = true;
      axios
        .get<LoadDataDatabaseResponse>(this.urls.load_data_database)
        .then((response) => {
          this.dataBaseData = response.data.dataInfo;
          this.selectLabelTable = this.translate.label_list_field;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Execute une requête SQL
     */
    execute() {
      if (!this.isValidate()) {
        return;
      }

      this.loading = true;
      axios
        .post<ExecuteSqlResponse>(this.urls.execute_sql, {
          query: this.sqlManager.query,
        })
        .then((response) => {
          this.error = response.data.data.error;
          this.result = response.data.data.result;
          this.resultHeader = response.data.data.header;

          if (this.error === '') {
            this.toasts.toastSuccess.show = true;
            this.toasts.toastSuccess.msg = this.translate.toast_msg_exec_success;
          } else {
            this.toasts.toastError.show = true;
            this.toasts.toastError.msg = this.translate.toast_msg_exec_error;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Sauvegarde une query
     */
    save() {
      if (!this.isValidate()) {
        return;
      }

      this.loading = true;
      axios
        .post<SaveResponse>(this.urls.save, {
          query: this.sqlManager.query,
          name: this.sqlManager.name,
          id: this.sqlManager.id,
        })
        .then((response) => {
          if (response.data.success) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
            // Cas première query, on force la redirection pour passer en mode édition
            if (response.data.redirect && response.data.url_redirect) {
              window.location.replace(response.data.url_redirect);
            }
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
          emitter.emit('reset-check-confirm');
        });
    },

    /**
     * Appelé au changement de sélection dans la liste des tables,
     * charge les colonnes de la dernière table sélectionnée
     */
    onTableChange() {
      const lastSelected = this.selectTable[this.selectTable.length - 1];
      if (!lastSelected) {
        this.selectColumns = [];
        this.selectLabelTable = this.translate.label_list_field;
        return;
      }
      this.loadColumn(lastSelected);
    },

    /**
     * Retourne la liste des colonnes en fonction d'une table
     * @param tableName
     */
    loadColumn(tableName: string) {
      this.selectLabelTable = this.translate.label_list_field_2 + ' ' + tableName;
      const table = this.dataBaseData.find((t) => t.name === tableName);
      this.selectColumns = table ? table.columns : [];
    },

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast: 'toastSuccess' | 'toastError') {
      this.toasts[nameToast].show = false;
    },

    /**
     * Affichage ou masque le query builder
     */
    renderQueryBuilder() {
      this.showQueryBuilder = !this.showQueryBuilder;
    },

    /**
     * Renvoi true si tout est ok, false sinon
     */
    isValidate(): boolean {
      this.isErrorValidateQuery = !this.sqlManager.query;
      this.isErrorValidateName = !this.sqlManager.name;

      return !(this.isErrorValidateName || this.isErrorValidateQuery);
    },

    /**
     * Ajoute un élément dans l'input
     * @param text
     * @param separate
     */
    addElement(text: string, separate: boolean) {
      const input = document.getElementById('sql-textarea') as HTMLTextAreaElement | null;
      if (!input) {
        return;
      }

      const start = input.selectionStart ?? 0;
      const end = input.selectionEnd ?? 0;
      const value = this.sqlManager.query ?? '';
      const selection = window.getSelection()?.toString() ?? '';

      if (selection === '') {
        this.sqlManager.query = value.slice(0, start) + text + value.slice(end);
      } else {
        const before = value.slice(0, start);
        const after = value.slice(end);
        let replace: string;

        if (separate) {
          const half = text.slice(text.length / 2);
          replace = half + selection + half;
        } else {
          replace = text + selection;
        }

        this.sqlManager.query = before + replace + after;
      }

      input.value = this.sqlManager.query;
      input.focus();
    },

    /**
     * Ajoute les tables sélectionnées dans la query
     */
    addTable() {
      if (this.selectTable.length === 0) {
        return;
      }
      const tables = this.selectTable.map((table) => `${this.schema}.${table}`).join(', ');
      this.addElement(tables, false);
    },

    /**
     * Ajoute les champs sélectionnés dans la query
     */
    addField() {
      if (this.selectField.length === 0) {
        return;
      }
      this.addElement(this.selectField.join(', '), false);
    },
  },
});
</script>

<template>
  <div v-if="loading">
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-text :nb-paragraphe="3" />
    </div>
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-text :nb-paragraphe="3" />
    </div>
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-search-result :rows="2" />
    </div>
  </div>

  <div v-else-if="Object.keys(sqlManager).length === 0">
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
      <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
        style="background-color: var(--primary-lighter)"
      >
        <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="m8 9 3 3-3 3m5 0h3M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"
          />
        </svg>
      </div>

      <p class="text-lg font-bold mb-2" style="color: var(--text-primary)">
        {{ translate.no_query_manager_title }}
      </p>

      <p class="text-sm max-w-xs mb-6" style="color: var(--text-secondary)">
        {{ translate.no_query_manager_text }}
      </p>

      <div class="flex items-center gap-3">
        <a :href="urls.index" class="btn btn-sm btn-outline-dark flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ translate.btn_back }}
        </a>
        <a :href="urls.new" class="btn btn-sm btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ translate.btn_new }}
        </a>
      </div>
    </div>
  </div>

  <div v-else>
    <div class="card mb-4">
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
              style="color: var(--primary)"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 6c0 1.657-3.134 3-7 3S5 7.657 5 6m14 0c0-1.657-3.134-3-7-3S5 4.343 5 6m14 0v6M5 6v6m0 0c0 1.657 3.134 3 7 3s7-1.343 7-3M5 12v6c0 1.657 3.134 3 7 3s7-1.343 7-3v-6"
              />
            </svg>

            {{ translate.title_my_query }}
          </div>
          <p class="card-subtitle">
            {{ translate.sub_title_my_query }}
          </p>
        </div>

        <div class="card-actions">
          <div class="btn btn-success btn-sm me-2" @click="execute">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"
              ></path>
            </svg>
            {{ translate.btn_execute_query }}
          </div>

          <div class="btn btn-primary btn-sm me-2" @click="save">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
              ></path>
            </svg>
            {{ translate.btn_save_query }}
          </div>
        </div>
      </div>
      <div class="p-5">
        <div class="form-control mb-3">
          <label for="name-query" class="form-label">{{ translate.label_name }} *</label>
          <input
            type="text"
            class="form-input"
            :class="isErrorValidateName ? 'is-invalid' : ''"
            id="name-query"
            :placeholder="translate.label_name_placeholder"
            v-model="sqlManager.name"
          />
          <div v-if="isErrorValidateName" class="form-text text-error">
            {{ translate.error_name_empty }}
          </div>
        </div>

        <div class="form-control mb-3">
          <label for="sql-textarea" class="form-label">{{ translate.label_textarea_query }}</label>
          <textarea
            class="form-input code-editor"
            :class="isErrorValidateQuery ? 'is-invalid' : ''"
            id="sql-textarea"
            rows="10"
            v-model="sqlManager.query"
          ></textarea>
          <div v-if="isErrorValidateQuery" class="form-text text-error">
            {{ translate.error_query_empty }}
          </div>
        </div>

        <alert-danger v-if="error !== ''" type="alert-danger-solid mb-3" :text="error" />
        <alert-primary type="alert-primary-solid" :text="translate.help_text_1" />
      </div>
    </div>

    <div class="card mb-4">
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
              style="color: var(--primary)"
            >
              <path stroke="currentColor" stroke-width="2" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
            </svg>

            {{ translate.bloc_query }}
          </div>

          <p class="card-subtitle">
            {{ translate.bloc_query_sub_title }}
          </p>
        </div>

        <div class="card-actions">
          <div class="btn btn-success btn-sm me-2" @click="execute">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"
              ></path>
            </svg>
            {{ translate.btn_execute_query }}
          </div>

          <div class="btn btn-primary btn-sm me-2" @click="save">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
              ></path>
            </svg>
            {{ translate.btn_save_query }}
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-5">
        <div>
          <h3 class="text-sm font-semibold mb-3 text-[var(--text-primary)]">{{ translate.label_list_table }}</h3>
          <div class="form-control mb-3">
            <input type="text" class="form-input" v-model="searchTable" :placeholder="translate.placeholder_table" />
          </div>
          <div class="form-control mb-3">
            <select class="form-input" multiple id="sql-table" size="8" v-model="selectTable" @change="onTableChange">
              <option v-for="table in filteredTable" :key="table.name" :value="table.name">
                {{ table.name }}
              </option>
            </select>
          </div>
          <div class="mb-3">
            <div class="btn btn-secondary btn-sm w-full" @click="addTable">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              {{ translate.btn_add_table }}
            </div>
          </div>
          <p class="text-xs text-[var(--text-secondary)]">{{ translate.help_select_table }}</p>
        </div>

        <div>
          <h3 class="text-sm font-semibold mb-3 text-[var(--text-primary)]">{{ selectLabelTable }}</h3>
          <div class="form-control mb-3">
            <input
              type="text"
              class="form-input"
              v-model="searchField"
              :placeholder="translate.placeholder_field"
              :disabled="selectColumns.length === 0"
            />
          </div>
          <div class="form-control mb-3">
            <select
              class="form-input"
              multiple
              id="sql-field"
              size="8"
              v-model="selectField"
              :disabled="selectColumns.length === 0"
            >
              <option v-for="column in filteredFieldName" :key="column" :value="column">
                {{ column }}
              </option>
            </select>
          </div>
          <div class="form-control mb-3">
            <button :disabled="selectColumns.length === 0" class="btn btn-secondary btn-sm w-full" @click="addField">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              {{ translate.btn_add_table }}
            </button>
          </div>
          <p class="text-xs text-[var(--text-secondary)]">
            {{ translate.help_select_field }}
          </p>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary)">
              <path stroke="currentColor" stroke-width="2" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
            </svg>

            {{ translate.bloc_result }}
          </div>

          <p class="card-subtitle">
            {{ translate.bloc_result_sub_title }}
          </p>
        </div>
      </div>

      <div v-if="!result.length" class="text-center py-12 text-[var(--text-secondary)]">
        <svg
          class="w-16 h-16 mx-auto mb-4 text-[var(--text-light)]"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          ></path>
        </svg>
        <p class="text-sm font-medium">{{ translate.no_result_query }}</p>
        <p class="text-xs mt-1">{{ translate.no_result_query_help }}</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full" aria-describedby="table">
          <thead class="bg-[var(--bg-main)]">
            <tr>
              <th
                v-for="header in resultHeader"
                :key="header"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--text-secondary)]"
              >
                {{ header }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border-color)]">
            <tr v-for="(row, index) in result" :key="index" class="bg-[var(--bg-card)] hover:bg-[var(--bg-hover)]">
              <td
                v-for="header in resultHeader"
                :key="header"
                class="px-3 py-1 text-sm text-[var(--text-secondary)] text-left"
              >
                {{ row[header] }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="toasts.toastSuccess.show"
      @close-toast="closeToast"
    >
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="toasts.toastError.show"
      @close-toast="closeToast"
    >
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style>
.code-editor {
  background-color: #1e293b;
  color: #e2e8f0;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
  border-radius: 0.5rem;
  padding: 1rem;
  min-height: 200px;
  resize: vertical;
  border: 1px solid var(--border-dark);
}

.code-editor:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}
</style>

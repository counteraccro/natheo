<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Gestionnaire de requete SQL
 */

import axios from 'axios';
import Toast from '../../../Components/Global/Toast.vue';
import { emitter } from '@/utils/useEvent';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import SkeletonTabs from '@/vue/Components/Skeleton/Tabs.vue';
import SkeletonSearchResult from '@/vue/Components/Skeleton/SearchResult.vue';
import AlertPrimary from '@/vue/Components/Alert/Primary.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';

export default {
  name: 'SqlManager',
  components: { AlertDanger, AlertPrimary, SkeletonSearchResult, SkeletonTabs, SkeletonText, Toast },
  props: {
    urls: Object,
    translate: Object,
    id: Number,
    isExecute: Boolean,
    schema: String,
  },
  data() {
    return {
      loading: false,
      sqlManager: Object,
      dataBaseData: Object,
      selectTable: '',
      selectLabelTable: '',
      selectField: '',
      selectColumns: [],
      searchTable: '',
      searchField: '',
      result: Object,
      resultHeader: Object,
      error: '',
      isErrorValidateName: false,
      isErrorValidateQuery: false,
      showQueryBuilder: true,
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
    this.loadSqlManager();
    this.loadDataDatabase();
  },
  computed: {
    /**
     * Filtre sur tables
     * @returns {ObjectConstructor}
     */
    filteredTable() {
      const searchTable = this.searchTable && this.searchTable.toLowerCase();
      let data = this.dataBaseData;
      if (searchTable) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row.name).toLowerCase().indexOf(searchTable) > -1;
          });
        });
      }
      return data;
    },

    /**
     * Filtre sur champs d'une table
     * @returns {ObjectConstructor}
     */
    filteredFieldName() {
      const searchTable = this.searchField && this.searchField.toLowerCase();
      let data = this.selectColumns;
      if (searchTable) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row).toLowerCase().indexOf(searchTable) > -1;
          });
        });
      }
      return data;
    },
  },
  methods: {
    /**
     * Chargement des données SQLManager
     */
    loadSqlManager() {
      this.loading = true;
      axios
        .get(this.urls.load_sql_manager)
        .then((response) => {
          this.sqlManager = response.data.sqlManager;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;

          if (this.isExecute) {
            //this.isExecute = false;
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
        .get(this.urls.load_data_database)
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
        return false;
      }

      this.loading = true;
      axios
        .post(this.urls.execute_sql, {
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
        return false;
      }

      this.loading = true;
      axios
        .post(this.urls.save, {
          query: this.sqlManager.query,
          name: this.sqlManager.name,
          id: this.sqlManager.id,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
            // Cas première query, on force la redirection pour passer en mode édition
            if (response.data.redirect === true) {
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
     * Retourne la liste des colonnes en fonction d'une table
     * @param selectTable
     */
    loadColumn(selectTable) {
      this.selectLabelTable = this.translate.label_list_field_2 + ' ' + selectTable;
      this.dataBaseData.forEach((table) => {
        if (table.name === selectTable) {
          this.selectColumns = table.columns;
          return false;
        }
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
     * Affichage ou masque le query builder
     */
    renderQueryBuilder() {
      this.showQueryBuilder = !this.showQueryBuilder;
    },

    /**
     * Renvoi true si tout est ok, false sinon
     * @returns {boolean}
     */
    isValidate() {
      this.isErrorValidateQuery = this.sqlManager.query === null || this.sqlManager.query === '';
      this.isErrorValidateName = this.sqlManager.name === null || this.sqlManager.name === '';

      return !(this.isErrorValidateName || this.isErrorValidateQuery);
    },

    /**
     * Ajoute un élément dans l'input
     * @param balise
     * @param separate
     * @returns {boolean}
     */
    addElement(balise, separate) {
      let input = document.getElementById('sql-textarea');
      let start = input.selectionStart;
      let end = input.selectionEnd;
      let value = this.sqlManager.query;

      let select = window.getSelection().toString();

      if (select === '') {
        this.sqlManager.query = value.slice(0, start) + balise + value.slice(end);
        input.value = this.sqlManager.query;
        let caretPos = start + balise.length;
        input.focus();
        //input.setSelectionRange(caretPos - position, caretPos - position);
      } else {
        let before = value.slice(0, start);
        let after = value.slice(end);
        let replace = '';

        if (separate) {
          let b = balise.slice(balise.length / 2);
          replace = b + select.toString() + b;
        } else {
          replace = balise + select.toString();
        }

        this.sqlManager.query = before + replace + after;
        input.value = this.sqlManager.query;

        let caretPos = start + replace.length;
        input.focus();
      }
      return false;
    },
  },
};
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

  <div v-else>
    <div class="card rounded-lg p-6 mb-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <div class="flex justify-between">
          <h2 class="text-lg font-bold text-[var(--text-primary)]">
            {{ translate.title_my_query }}
          </h2>
          <div>
            <div class="btn btn-success btn-sm me-2" @click="this.execute()">
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
            <div class="btn btn-primary btn-sm me-2" @click="this.save">
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
        <div class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
          {{ translate.sub_title_my_query }}
        </div>
      </div>

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

    <div class="card rounded-lg p-6 mb-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <div class="flex justify-between">
          <h2 class="text-lg font-bold text-[var(--text-primary)]">
            {{ translate.bloc_query }}
          </h2>
          <div>
            <div class="btn btn-success btn-sm me-2" @click="execute()">
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
        <div class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
          {{ translate.bloc_query_sub_title }}
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="text-sm font-semibold mb-3 text-[var(--text-primary)]">{{ translate.label_list_table }}</h3>
          <div class="form-control mb-3">
            <input type="text" class="form-input" v-model="searchTable" :placeholder="translate.placeholder_table" />
          </div>
          <div class="form-control mb-3">
            <select class="form-input" multiple id="sql-table" size="8" v-model="selectTable">
              <option v-for="table in filteredTable" @click="loadColumn(table.name)">
                {{ table.name }}
              </option>
            </select>
          </div>
          <div class="mb-3">
            <div class="btn btn-secondary btn-sm w-full" @click="addElement(schema + '.' + selectTable, false)">
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
              <option v-for="column in filteredFieldName">
                {{ column }}
              </option>
            </select>
          </div>
          <div class="form-control mb-3">
            <button
              :disabled="selectColumns.length === 0"
              class="btn btn-secondary btn-sm w-full"
              @click="addElement(selectField, false)"
            >
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

    <div class="card rounded-lg p-6 mb-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <div class="flex justify-between">
          <h2 class="text-lg font-bold text-[var(--text-primary)]">
            {{ translate.bloc_result }}
          </h2>
        </div>
        <div class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
          {{ translate.bloc_result_sub_title }}
        </div>
      </div>

      <div v-if="!Object.keys(result).length" class="text-center py-12 text-[var(--text-secondary)]">
        <svg
          class="w-16 h-16 mx-auto mb-4 text-[var(-text-light)]"
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

      <div class="overflow-x-auto">
        <table class="w-full" aria-describedby="table">
          <thead class="bg-[var(--bg-main)]">
            <tr>
              <th
                v-for="header in resultHeader"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--text-secondary)]"
              >
                {{ header }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border-color)]">
            <tr v-for="row in result" class="bg-[var(--bg-card)] hover:bg-[var(--bg-hover)]">
              <td v-for="header in resultHeader" class="px-3 py-1 text-sm text-[var(--text-secondary)] text-left">
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

<script>

import axios from "axios";
import Toast from "../../../Components/Global/Toast.vue";

export default {
  name: "SqlManager",
  components: {Toast},
  props: {
    urls: Object,
    translate: Object,
    id: Number
  },
  data() {
    return {
      loading: false,
      sqlManager: Object,
      dataBaseData: Object,
      selectTable: '',
      selectField: '',
      selectColumns: [],
      searchTable: '',
      searchField: '',
      result: Object,
      resultHeader: Object,
      error: '',
      showHelp: false,
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      },
    }
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

      const searchTable = this.searchTable && this.searchTable.toLowerCase()
      let data = this.dataBaseData
      if (searchTable) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row.name).toLowerCase().indexOf(searchTable) > -1
          })
        })
      }
      return data;
    },

    /**
     * Filtre sur champs d'une table
     * @returns {ObjectConstructor}
     */
    filteredFieldName() {

      const searchTable = this.searchField && this.searchField.toLowerCase()
      let data = this.selectColumns
      if (searchTable) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row).toLowerCase().indexOf(searchTable) > -1
          })
        })
      }
      return data;
    }
  },
  methods: {

    /**
     * Chargement des données SQLManager
     */
    loadSqlManager() {
      this.loading = true;
      axios.get(this.urls.load_sql_manager + '/' + this.id).then((response) => {
        this.sqlManager = response.data.sqlManager;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Charge les informations de la base de données
     */
    loadDataDatabase() {
      this.loading = true;
      axios.get(this.urls.load_data_database).then((response) => {
        this.dataBaseData = response.data.dataInfo;
        this.selectTable = this.translate.label_list_field;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Execute une requête SQL
     */
    execute() {
      this.loading = true;
      axios.post(this.urls.execute_sql, {
        query: this.sqlManager.query
      }).then((response) => {
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

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    loadColumn(selectTable) {
      this.selectTable = this.translate.label_list_field_2 + ' ' + selectTable;
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
      this.toasts[nameToast].show = false
    },
  }
}

</script>

<template>
  <div id="block-sql-manager" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="card mb-3" v-if="this.showHelp">
      <div class="card-header">
        Featured
        <button type="button" class="btn-close float-end" aria-label="Close" @click="this.showHelp = false"></button>
      </div>
      <div class="card-body">
        <h5 class="card-title">Special title treatment</h5>
        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>

    <div class="btn btn-sm btn-secondary float-end mb-1" @click="this.showHelp = true"><i class="bi bi-info-circle"></i>
    </div>

    <!--<div class="alert alert-warning">
      <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close"></button>
      <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> {{ this.translate.alert_waring_title }}
      </h4>
      <p>
        {{ this.translate.alert_waring_msg }}
      </p>
    </div>-->

    <div v-if="this.error !== ''" class="alert alert-danger">
      {{ this.error }}
    </div>

    <div>
      <label for="sql-textarea" class="form-label">{{ this.translate.label_textarea_query }}</label>
      <textarea class="form-control" id="sql-textarea" rows="10" v-model="this.sqlManager.query"></textarea>
      <div class="float-end mt-2">
        <div class="btn btn-secondary me-2" @click="this.execute()">
          <i class="bi bi-terminal"></i> {{ this.translate.btn_execute_query }}
        </div>
        <div class="btn btn-secondary me-2"><i class="bi bi-floppy"></i> {{ this.translate.btn_save_query }}</div>
        <div class="btn btn-secondary"><i class="bi bi-eye-slash"></i> {{ this.translate.btn_disabled_query }}</div>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="card mt-4">
      <div class="card-header">
        {{ this.translate.bloc_query }}
        <div class="float-end btn btn-secondary btn-sm">
          <i class="bi bi-chevron-up"></i>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <label for="sql-table" class="form-label">{{ this.translate.label_list_table }}</label>
            <input type="text" class="form-control" v-model="this.searchTable" :placeholder="this.translate.placeholder_table">
            <select class="form-select" multiple id="sql-table" size="8">
              <option v-for="(table) in filteredTable" @click="this.loadColumn(table.name)">
                {{ table.name }}
              </option>
            </select>
            <div class="btn btn-secondary btn-sm mt-1 float-end">
              {{ this.translate.btn_add_table }}
            </div>
          </div>
          <div class="col-6">
            <label for="sql-field" class="form-label">{{ this.selectTable }}</label>
            <input type="text" class="form-control" v-model="this.searchField" :placeholder="this.translate.placeholder_field">
            <select class="form-select" multiple id="sql-field" size="8">
              <option v-for="(column) in filteredFieldName">
                {{ column }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        {{ this.translate.bloc_result }}
      </div>
      <div class="card-body overflow-x-auto">

        <div class="table-responsive">
          <table class="table table-sm table-striped table-hover" aria-describedby="table">
            <thead>
            <tr>
              <th v-for="header in this.resultHeader">
                {{ header }}
              </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in this.result">
              <td v-for="header in this.resultHeader">
                {{ row[header] }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
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
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>
</template>
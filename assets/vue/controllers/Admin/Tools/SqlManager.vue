<script>

import axios from "axios";
import tab from "bootstrap/js/src/tab";

export default {
  name: "SqlManager",
  props: {
    urls: Object,
    translate: Object,
    id: Number
  },
  data() {
    return {
      loading: false,
      sqlManager: Object,
      dataBaseData : Object,
      selectTable : '',
      selectField : '',
      selectColumns : [],
      searchTable: '',
      searchField: '',
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
    execute()
    {
      this.loading = true;
      axios.post(this.urls.execute_sql, {
        query : this.sqlManager.query
      }).then((response) => {

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    loadColumn(selectTable)
    {
      this.selectTable = this.translate.label_list_field_2 + ' ' + selectTable;
      this.dataBaseData.forEach((table) => {
          if(table.name === selectTable)
          {
            this.selectColumns = table.columns;
            return false;
          }
      });
    }
  }
}

</script>

<template>
  <div id="block-sql-manager" :class="this.loading === true ? 'block-grid' : ''">

    <div class="alert alert-warning">
      <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close"></button>
      <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> {{ this.translate.alert_waring_title }}</h4>
      <p>
        {{ this.translate.alert_waring_msg }}
      </p>
    </div>

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div>
      <label for="sql-textarea" class="form-label">{{ this.translate.label_textarea_query }}</label>
      <textarea class="form-control" id="sql-textarea" rows="10" v-model="this.sqlManager.query"></textarea>
      <div class="float-end mt-2">
        <div class="btn btn-secondary me-2" @click="this.execute()"><i class="bi bi-terminal"></i> {{ this.translate.btn_execute_query }}</div>
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
        <div class="card-body">

        </div>
      </div>

    </div>
</template>
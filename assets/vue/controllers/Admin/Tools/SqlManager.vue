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

    loadDataDatabase() {
      this.loading = true;
      axios.get(this.urls.load_data_database).then((response) => {
       this.dataBaseData = response.data.dataInfo;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    loadColumn(selectTable)
    {
      this.selectTable = selectTable;
      this.dataBaseData.forEach((table) => {
          if(table.name === this.selectTable)
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

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div>
      <label for="sql-textarea" class="form-label">Example textarea</label>
      <textarea class="form-control" id="sql-textarea" rows="10"></textarea>
      <div class="float-end mt-2">
        <div class="btn btn-secondary me-2">aaa</div>
        <div class="btn btn-secondary me-2">aaa</div>
        <div class="btn btn-secondary">aaa</div>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="card mt-4">
      <div class="card-header">
        Featured
        <div class="float-end btn btn-secondary btn-sm">aa</div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <label for="sql-table" class="form-label">Example textarea</label>
            <input type="text" class="form-control" v-model="this.searchTable" placeholder="aaa">
            <select class="form-select" multiple id="sql-table">
              <option v-for="(table) in filteredTable" @click="this.loadColumn(table.name)">
                {{ table.name }}
              </option>
            </select>
          </div>
          <div class="col-6">
            <label for="sql-field" class="form-label">{{ this.selectTable }}</label>
            <input type="text" class="form-control" v-model="this.searchField" placeholder="aaa">
            <select class="form-select" multiple id="sql-field">
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
          Featured
        </div>
        <div class="card-body">

        </div>
      </div>

    </div>
</template>
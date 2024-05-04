<script>

import axios from "axios";
import tab from "bootstrap/js/src/tab";

export default {
  name: "SqlManager",
  computed: {
    tab() {
      return tab
    }
  },
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
      selectColumns : []
    }
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
            <select class="form-select" multiple id="sql-table">
              <option v-for="(table) in this.dataBaseData" @click="this.loadColumn(table.name)">
                {{ table.name }}
              </option>
            </select>
          </div>
          <div class="col-6">
            <label for="sql-table" class="form-label">Example textarea</label>
            <select class="form-select" multiple id="sql-table">
              <option selected>Open this select menu</option>
              <option value="1">One</option>
              <option value="2">Two</option>
              <option value="3">Three</option>
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
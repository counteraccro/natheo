<script>

import axios from "axios";

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
      sqlManager: Object
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
        //this.sqlManager = response.data.sqlManager;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
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

  </div>
</template>
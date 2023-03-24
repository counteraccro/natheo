<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher les logs sous forme de tableau d'après les fichiers de logs
 */
import axios from "axios";

export default {
  name: "Log",
  props: {
    url_select: String,
    url_load_log_file: String
  },
  data() {
    return {
      select: [],
      time: "",
      trans: []
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      console.log(this.time);
      axios.post(this.url_select, {
        'time' : this.time
      }).then((response) => {
        this.select = response.data.files;
        this.trans = response.data.trans;
      }).catch((error) => {
        console.log(error);
      }).finally();
    },
    changeTimeFiltre(event)
    {
        this.time = event.target.value;
        this.loadData();
    },

    /**
     * Charge le contenu d'un fichier log
     * @param event
     */
    selectLogFile(event)
    {
      axios.post(this.url_load_log_file, {
        'file' : event.target.value
      }).then((response) => {


      }).catch((error) => {
        console.log(error);
      }).finally();
    }
  }
}
</script>

<template>
  <div class="row">
    <div class="col">
      <select class="form-select" @change="selectLogFile($event)">
        <option selected>{{this.trans.log_select_file}}</option>
        <option v-for="option in this.select" v-bind:value="option.path">{{ option.name }}</option>
      </select>
    </div>
    <div class="col">
      <select class="form-select" @change="changeTimeFiltre($event)">
        <option value="" selected>{{this.trans.log_select_time_all}}</option>
        <option value="now">{{this.trans.log_select_time_now}}</option>
        <option value="yesterday">{{this.trans.log_select_time_yesterday}}</option>
      </select>
    </div>
  </div>

</template>

<style scoped>

</style>
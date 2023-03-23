<script>

import axios from "axios";

export default {
  name: "Log",
  props: {
    url_select: String
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
    }
  }
}
</script>

<template>
  <div class="row">
    <div class="col">
      <select class="form-select" aria-label="Default select example">
        <option selected>{{this.trans.log_select_file}}</option>
        <option v-for="option in this.select" value="{{option.path}}">{{ option.name }}</option>
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
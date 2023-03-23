<script>

import axios from "axios";

export default {
  name: "Log",
  props: {
    url_select: String
  },
  data() {
    return {
      select : [],
    }
  },
  mounted() {
    this.loadSelect();
  },
  methods: {
    loadSelect() {
      axios.post(this.url_select, {}).then((response) => {
          this.select = response.data.files
        console.log(this.select);
      }).catch((error) => {
        console.log(error);
      }).finally();
    }
  }
}
</script>

<template>
  <select class="form-select" aria-label="Default select example">
    <option selected>Open this select menu</option>
    <template v-for="option in this.select">
      <optgroup v-if="option.type === 'dir'" v-bind:label="option.name"></optgroup>
      <option v-if="option.type ==='file' " value="{{option.path}}">{{option.name}}</option>
    </template>
  </select>
</template>

<style scoped>

</style>
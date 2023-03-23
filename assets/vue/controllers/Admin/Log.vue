<script>

import axios from "axios";

export default {
  name: "Log",
  props: {
    url_select: String
  },
  mounted() {
    this.loadSelect();
  },
  methods: {
    loadSelect() {
      axios.post(this.url_select, {}).then((response) => {

      }).catch((error) => {
        console.log(error);
      }).finally();
    }
  }
}
</script>

<template>

</template>

<style scoped>

</style>
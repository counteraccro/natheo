<script>
import {round} from "@popperjs/core/lib/utils/math";

export default {
  name: "GridPaginate",
  props: {
    currentPage: Number,
    nbElementsTotal: Number,
    nbElements: Number,
    url: String
  },
  emits: ['change-page-event'],
  data() {
    return {}
  },
  methods: {
    getNbPage() {
      return round(this.nbElementsTotal / this.nbElements);
    },
  }
}

</script>

<template>
  <div>
    <div v-for="(n, i) in this.getNbPage()">
      <button class="btn btn-primary"  @click="$emit('change-page-event', n)">Page {{ n }}</button>
    </div>
    Nombre de page : {{ this.nbPage }}
    Nombre de Element total : {{ this.nbElementsTotal }}
    Nombre de Element : {{ this.nbElements }}
  </div>

</template>

<style scoped>

</style>
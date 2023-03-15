<script>
export default {
  name: "GridPaginate",
  props: {
    currentPage: Number,
    nbElementsTotal: Number,
    nbElements: Number,
    url: String,
    listLimit: Array
  },
  emits: ['change-page-event'],
  data() {
    return {
      cPage: this.currentPage,
      cLimit: this.nbElements
    }
  },
  methods: {
    getNbPage() {
      return Math.ceil(this.nbElementsTotal / this.cLimit);
    },

    updateCurrentPage(page) {
      this.cPage = page;
    }
  }
}

</script>

<template>
  <nav>
    <div class="float-md-end">
      Page <strong>{{ cPage }}</strong> sur
      <strong>{{ this.getNbPage() }}</strong> - {{ this.nbElementsTotal }} elements
    </div>

    <div class="float-end me-3">
      <select class="form-select form-select-sm" aria-label="Default select example" v-model="cLimit" @change="$emit('change-page-event', 1, cLimit)">
        <option v-for="(i) in this.listLimit" :value="i">{{i}}</option>
      </select>
    </div>


    <ul class="pagination pagination-sm">
      <li class="page-item" :class="cPage === 1 ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', 1, cLimit), updateCurrentPage(1)]">
          <i class="bi bi-chevron-double-left"></i>
        </button>
      </li>
      <li class="page-item" :class="cPage === 1 ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', (cPage-1), cLimit), updateCurrentPage((cPage-1))]">
          <i class="bi bi-chevron-compact-left"></i>
        </button>
      </li>
      <li class="page-item" v-for="(n, i) in this.getNbPage()" :class="cPage === n ? 'active' : ''">
        <button v-if="n === cPage-1 || n === cPage+1 || n === cPage || n <= 2 || n >= this.getNbPage()-1" class="page-link" @click="[$emit('change-page-event', n, cLimit), updateCurrentPage(n)]">{{ n }}</button>
        <button v-else-if="n === cPage-2 || n === cPage+2" class="page-link disabled">...</button>
      </li>
      <li class="page-item" :class="cPage === this.getNbPage() ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', (cPage+1), cLimit), updateCurrentPage((cPage+1))]">
          <i class="bi bi-chevron-compact-right"></i>
        </button>
      </li>
      <li class="page-item" :class="cPage === this.getNbPage() ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', this.getNbPage(), cLimit), updateCurrentPage(this.getNbPage())]">
          <i class="bi bi-chevron-double-right"></i>
        </button>
      </li>
    </ul>

  </nav>
</template>

<style scoped>

</style>
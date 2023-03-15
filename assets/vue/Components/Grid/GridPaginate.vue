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
    return {
      cPage : this.currentPage
    }
  },
  methods: {
    getNbPage() {
      return round(this.nbElementsTotal / this.nbElements);
    },

    updateCurrentPage(page)
    {
      this.cPage = page;
    }
  }
}

</script>

<template>
  <nav>
    <div class="float-end">Page <strong>{{cPage}}</strong> sur <strong>{{this.getNbPage()}}</strong> - {{this.nbElementsTotal}} elements</div>
    <ul class="pagination pagination-sm">
      <li class="page-item" :class="cPage === 1 ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', 1), updateCurrentPage(1)]">
          <i class="bi bi-chevron-double-left"></i>
        </button>
      </li>
      <li class="page-item" :class="cPage === 1 ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', (cPage-1)), updateCurrentPage((cPage-1))]">
          <i class="bi bi-chevron-compact-left"></i>
        </button>
      </li>
      <li class="page-item" v-for="(n, i) in this.getNbPage()" :class="cPage === n ? 'active' : ''">
        <button v-if="n === cPage-1 || n === cPage+1 || n === cPage || n <= 2 || n >= this.getNbPage()-1" class="page-link" @click="[$emit('change-page-event', n), updateCurrentPage(n)]">{{ n }}</button>
        <button v-else-if="n === cPage-2 || n === cPage+2" class="page-link disabled">...</button>
      </li>
      <li class="page-item" :class="cPage === this.getNbPage() ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', (cPage+1)), updateCurrentPage((cPage+1))]">
          <i class="bi bi-chevron-compact-right"></i>
        </button>
      </li>
      <li class="page-item" :class="cPage === this.getNbPage() ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', this.getNbPage()), updateCurrentPage(this.getNbPage())]">
          <i class="bi bi-chevron-double-right"></i>
        </button>
      </li>
    </ul>
  </nav>
</template>

<style scoped>

</style>
<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher la pagination pour la recherche
 */

export default {
  name: "SearchPaginate",
  props: {
    currentPage: Number,
    nbElementsTotal: Number,
    nbElements: String,
    translate: Object
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
    },
  }
}

</script>

<template>
  <nav>
    <div class="float-md-end">
      {{ translate.page }} <strong>{{ cPage }}</strong> {{ translate.on }}
      <strong>{{ this.getNbPage() }}</strong> - {{ this.nbElementsTotal }} {{ translate.row }}
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
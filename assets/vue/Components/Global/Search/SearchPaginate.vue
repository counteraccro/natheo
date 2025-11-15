<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher la pagination pour la recherche
 */

export default {
  name: 'SearchPaginate',
  props: {
    currentPage: Number,
    nbElementsTotal: Number,
    nbElements: Number,
    translate: Object,
  },
  emits: ['change-page-event'],
  data() {
    return {
      cPage: this.currentPage,
      cLimit: this.nbElements,
    };
  },
  methods: {
    getNbPage() {
      return Math.ceil(this.nbElementsTotal / this.cLimit);
    },

    updateCurrentPage(page) {
      this.cPage = page;
    },
  },
};
</script>

<template>
  <nav class="flex items-center justify-between mt-6">
    <div class="text-sm text-[var(--text-secondary)]">
      {{ translate.page }} <strong>{{ cPage }}</strong> {{ translate.on }} <strong>{{ this.getNbPage() }}</strong> -
      {{ this.nbElementsTotal }} {{ translate.row }}
    </div>

    <ul class="flex items-center -space-x-px h-8 text-sm">
      <li>
        <a
          href="#"
          @click="[$emit('change-page-event', 1, cLimit), updateCurrentPage(1)]"
          class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-[var(--primary)] bg-white border border-e-0 border-[var(--primary-hover)] rounded-s-lg hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="cPage === 1 ? 'disabled' : ''"
        >
          <span class="sr-only">Previous</span>
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m17 16-4-4 4-4m-6 8-4-4 4-4"
            />
          </svg>
        </a>
      </li>
      <li>
        <a
          href="#"
          @click="[$emit('change-page-event', cPage - 1, cLimit), updateCurrentPage(cPage - 1)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="cPage === 1 ? 'disabled' : ''"
        >
          <span class="sr-only">Previous</span>
          <svg
            class="w-2.5 h-2.5 rtl:rotate-180"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 6 10"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 1 1 5l4 4"
            />
          </svg>
        </a>
      </li>
      <li v-for="(n, i) in this.getNbPage()">
        <a
          v-if="n === cPage - 1 || n === cPage + 1 || n === cPage || n <= 2 || n >= this.getNbPage() - 1"
          href="#"
          @click="[$emit('change-page-event', n, cLimit), updateCurrentPage(n)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="cPage === n ? 'bg-[var(--primary-hover)] text-white' : 'bg-white text-[var(--primary)]'"
          >{{ n }}</a
        >
        <a
          href="#"
          v-else-if="n === cPage - 2 || n === cPage + 2"
          class="flex items-center justify-center px-3 h-8 leading-tight text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
        >
          ...
        </a>
      </li>
      <li>
        <a
          href="#"
          @click="[$emit('change-page-event', cPage + 1, cLimit), updateCurrentPage(cPage + 1)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="cPage === this.getNbPage() ? 'disabled' : ''"
        >
          <span class="sr-only">Next</span>
          <svg
            class="w-2.5 h-2.5 rtl:rotate-180"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 6 10"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m1 9 4-4-4-4"
            />
          </svg>
        </a>
      </li>
      <li>
        <a
          href="#"
          @click="[$emit('change-page-event', this.getNbPage(), cLimit), updateCurrentPage(this.getNbPage())]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight rounded-e-lg text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="cPage === this.getNbPage() ? 'disabled' : ''"
        >
          <span class="sr-only">Next</span>
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m7 16 4-4-4-4m6 8 4-4-4-4"
            />
          </svg>
        </a>
      </li>
    </ul>
  </nav>

  <!--<nav>
    <div class="float-md-end">
      {{ translate.page }} <strong>{{ cPage }}</strong> {{ translate.on }} <strong>{{ this.getNbPage() }}</strong> -
      {{ this.nbElementsTotal }} {{ translate.row }}
    </div>

    <ul class="pagination pagination-sm">
      <li class="page-item" :class="cPage === 1 ? 'disabled' : ''">
        <button class="page-link" @click="[$emit('change-page-event', 1, cLimit), updateCurrentPage(1)]">
          <i class="bi bi-chevron-double-left"></i>
        </button>
      </li>
      <li class="page-item" :class="cPage === 1 ? 'disabled' : ''">
        <button
          class="page-link"
          @click="[$emit('change-page-event', cPage - 1, cLimit), updateCurrentPage(cPage - 1)]"
        >
          <i class="bi bi-chevron-compact-left"></i>
        </button>
      </li>
      <li class="page-item" v-for="(n, i) in this.getNbPage()" :class="cPage === n ? 'active' : ''">
        <button
          v-if="n === cPage - 1 || n === cPage + 1 || n === cPage || n <= 2 || n >= this.getNbPage() - 1"
          class="page-link"
          @click="[$emit('change-page-event', n, cLimit), updateCurrentPage(n)]"
        >
          {{ n }}
        </button>
        <button v-else-if="n === cPage - 2 || n === cPage + 2" class="page-link disabled">...</button>
      </li>
      <li class="page-item" :class="cPage === this.getNbPage() ? 'disabled' : ''">
        <button
          class="page-link"
          @click="[$emit('change-page-event', cPage + 1, cLimit), updateCurrentPage(cPage + 1)]"
        >
          <i class="bi bi-chevron-compact-right"></i>
        </button>
      </li>
      <li class="page-item" :class="cPage === this.getNbPage() ? 'disabled' : ''">
        <button
          class="page-link"
          @click="[$emit('change-page-event', this.getNbPage(), cLimit), updateCurrentPage(this.getNbPage())]"
        >
          <i class="bi bi-chevron-double-right"></i>
        </button>
      </li>
    </ul>
  </nav>-->
</template>

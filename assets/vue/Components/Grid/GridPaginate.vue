<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher la pagination pour le grid
 */

export default {
  name: 'GridPaginate',
  props: {
    currentPage: Number,
    nbElementsTotal: Number,
    nbElements: String,
    url: String,
    listLimit: Object,
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
  <nav>
    <div class="btn-group">
      <button class="btn btn-primary btn-icon">
        <svg class="icon-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 19-7-7 7-7"
          />
        </svg>
      </button>
      <button class="btn btn-primary btn-sm">1</button>
      <button class="btn btn-primary btn-sm">2</button>
      <button class="btn btn-primary btn-sm">3</button>
      <button class="btn btn-primary btn-sm">
        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
    </div>

    <div class="float-md-end">
      {{ translate.page }} <strong>{{ cPage }}</strong> {{ translate.on }} <strong>{{ this.getNbPage() }}</strong> -
      {{ this.nbElementsTotal }} {{ translate.row }}
    </div>

    <div class="float-end me-3">
      <select
        class="form-select form-select-sm no-control"
        aria-label="Default select example"
        v-model="cLimit"
        @change="$emit('change-page-event', 1, cLimit)"
      >
        <option v-for="i in this.listLimit" :value="i">{{ i }}</option>
      </select>
    </div>

    <nav aria-label="Page navigation example">
      <ul class="flex items-center -space-x-px h-8 text-sm">
        <li>
          <a
            href="#"
            @click="[$emit('change-page-event', 1, cLimit), updateCurrentPage(1)]"
            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-[var(--primary)] bg-white border border-e-0 border-[var(--primary)] rounded-s-lg hover:bg-[var(--primary-hover)] hover:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
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
        <li>
          <a
            href="#"
            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >1</a
          >
        </li>
        <li>
          <a
            href="#"
            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >2</a
          >
        </li>
        <li>
          <a
            href="#"
            aria-current="page"
            class="z-10 flex items-center justify-center px-3 h-8 leading-tight text-blue-600 border border-blue-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white"
            >3</a
          >
        </li>
        <li>
          <a
            href="#"
            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >4</a
          >
        </li>
        <li>
          <a
            href="#"
            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >5</a
          >
        </li>
        <li>
          <a
            href="#"
            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
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
      </ul>
    </nav>

    <div class="btn-group">
      <button
        class="btn btn-outline-primary btn-sm"
        :disabled="cPage === 1 ? 'disabled' : ''"
        @click="[$emit('change-page-event', 1, cLimit), updateCurrentPage(1)]"
      >
        <svg class="icon-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m17 16-4-4 4-4m-6 8-4-4 4-4"
          />
        </svg>
      </button>
      <button
        class="btn btn-outline-primary btn-sm"
        :disabled="cPage === 1 ? 'disabled' : ''"
        @click="[$emit('change-page-event', cPage - 1, cLimit), updateCurrentPage(cPage - 1)]"
      >
        <svg class="icon-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 19-7-7 7-7"
          />
        </svg>
      </button>
      <span v-for="(n, i) in this.getNbPage()">
        <button
          v-if="n === cPage - 1 || n === cPage + 1 || n === cPage || n <= 2 || n >= this.getNbPage() - 1"
          class="btn btn-outline-primary btn-sm"
          @click="[$emit('change-page-event', n, cLimit), updateCurrentPage(n)]"
        >
          {{ n }}
        </button>
        <button v-else-if="n === cPage - 2 || n === cPage + 2" class="btn btn-outline-primary btn-sm">...</button>
      </span>
      <button
        :disabled="cPage === this.getNbPage() ? 'disabled' : ''"
        class="btn btn-outline-primary btn-sm"
        @click="[$emit('change-page-event', cPage + 1, cLimit), updateCurrentPage(cPage + 1)]"
      >
        <svg class="icon-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m9 5 7 7-7 7"
          />
        </svg>
      </button>
      <button
        :disabled="cPage === this.getNbPage() ? 'disabled' : ''"
        class="btn btn-outline-primary btn-sm"
        @click="[$emit('change-page-event', this.getNbPage(), cLimit), updateCurrentPage(this.getNbPage())]"
      >
        <svg class="icon-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m7 16 4-4-4-4m6 8 4-4-4-4"
          />
        </svg>
      </button>
    </div>
  </nav>
</template>

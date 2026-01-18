<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
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
  computed: {
    nbPages() {
      return Math.ceil(this.nbElementsTotal / this.cLimit);
    },

    /**
     * Génère le tableau d'affichage de la pagination
     * @returns {[]}
     */
    visiblePages() {
      const pages = [];
      const total = this.nbPages;
      const current = this.cPage;

      pages.push(1);

      const startPage = Math.max(2, current - 1);
      const endPage = Math.min(total - 1, current + 1);

      if (startPage > 2) {
        pages.push('...');
      }

      for (let i = startPage; i <= endPage; i++) {
        if (i !== 1 && i !== total) {
          pages.push(i);
        }
      }

      if (endPage < total - 1) {
        pages.push('...');
      }

      if (total > 1) {
        pages.push(total);
      }

      return pages;
    },
  },
  methods: {
    updateCurrentPage(page) {
      if (typeof page === 'number') {
        this.cPage = page;
      }
    },
  },
};
</script>

<template>
  <nav class="flex items-center justify-between mt-6">
    <div class="text-sm text-[var(--text-secondary)]">
      {{ translate.page }} <strong>{{ cPage }}</strong> {{ translate.on }} <strong>{{ nbPages }}</strong> -
      {{ nbElementsTotal }} {{ translate.row }}
    </div>

    <ul class="flex items-center -space-x-px h-8 text-sm">
      <li>
        <a
          href="#"
          @click.prevent="[$emit('change-page-event', 1, cLimit), updateCurrentPage(1)]"
          class="no-control flex items-center justify-center px-3 h-8 ms-0 leading-tight text-[var(--primary)] bg-white border border-e-0 border-[var(--primary-hover)] rounded-s-lg hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="{ 'opacity-50 pointer-events-none': cPage === 1 }"
        >
          <span class="sr-only">First</span>
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

      <!-- Page précédente -->
      <li>
        <a
          href="#"
          @click.prevent="[$emit('change-page-event', cPage - 1, cLimit), updateCurrentPage(cPage - 1)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="{ 'opacity-50 pointer-events-none': cPage === 1 }"
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

      <!-- Numéros de page -->
      <li v-for="(page, index) in visiblePages" :key="index">
        <a
          v-if="page === '...'"
          href="#"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight text-[var(--primary)] bg-white border border-[var(--primary-hover)] cursor-default dark:bg-[var(--btn-dark)]"
        >
          ...
        </a>

        <a
          v-else
          href="#"
          @click.prevent="[$emit('change-page-event', page, cLimit), updateCurrentPage(page)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="cPage === page ? 'bg-[var(--primary-hover)] text-white' : 'bg-white text-[var(--primary)]'"
        >
          {{ page }}
        </a>
      </li>

      <!-- Page suivante -->
      <li>
        <a
          href="#"
          @click.prevent="[$emit('change-page-event', cPage + 1, cLimit), updateCurrentPage(cPage + 1)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="{ 'opacity-50 pointer-events-none': cPage === nbPages }"
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

      <!-- Aller à la dernière page -->
      <li>
        <a
          href="#"
          @click.prevent="[$emit('change-page-event', nbPages, cLimit), updateCurrentPage(nbPages)]"
          class="no-control flex items-center justify-center px-3 h-8 leading-tight rounded-e-lg text-[var(--primary)] bg-white border border-[var(--primary-hover)] hover:bg-[var(--primary-hover)] hover:text-white dark:bg-[var(--btn-dark)]"
          :class="{ 'opacity-50 pointer-events-none': cPage === nbPages }"
        >
          <span class="sr-only">Last</span>
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

    <div>
      <select
        class="no-control form-input form-input-sm"
        v-model="cLimit"
        @change="$emit('change-page-event', 1, cLimit)"
      >
        <option v-for="i in listLimit" :key="i" :value="i">{{ i }}</option>
      </select>
    </div>
  </nav>
</template>

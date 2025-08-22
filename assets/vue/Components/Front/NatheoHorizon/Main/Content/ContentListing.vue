<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu de type listing
 */
export default {
  name: 'ContentLinsting',
  props: {
    data: Object,
    utilsFront: Object,
    locale: String,
    ajaxRequest: Object,
  },
  emits: ['api-failure'],
  data() {
    return {
      isLoad: false,
      limit: 2,
      page: 1,
      pages: '',
    }
  },
  created() {
    this.loadContent();
  },
  mounted() {

  },
  computed: {
  },
  methods: {
    loadContent() {
      let success = (datas) => {
        this.pages = datas.content.pages;
      }

      let loader = () => {
        this.isLoad = true
      }
      let params = {
        'id': this.data.id,
        'locale': this.locale,
        'limit': this.limit,
        'page': this.page
      };
      this.ajaxRequest.getContentPage(params, success, this.apiFailure, loader)
    },

    /**
     * Génère le lien
     * @param element
     * @returns {*}
     */
    generateUrl(element) {
      return this.utilsFront.getUrl(element);
    },

    /**
     * Change de page
     * @param page
     */
    changePage(page) {
      this.page = page;
      this.isLoad = false;
      this.loadContent();
    },

    getStylePagePagination() {
      return "px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-theme-4-750 hover:!text-theme-1-100 hover:dark:bg-gray-600 transition"
    },


    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },
  }
}
</script>

<template>
  <div v-if="this.isLoad">
    <!-- Liste d’articles -->
    <ul class="mx-auto divide-y divide-gray-200">

      <li v-for="page in this.pages" class="py-2">
        <a :href="this.generateUrl(page)" class="flex gap-4 hover:bg-theme-4-750/15  hover:dark:bg-gray-600 rounded-xl p-2 transition">
          <img v-if="page.img !== null" :src="page.img" :alt="page.title" class="h-28 w-40 flex-none rounded-xl object-cover" loading="lazy"/>
          <div v-else class="h-28 w-40 flex-none rounded-xl bg-gray-200 flex flex-col items-center justify-center text-gray-400 text-sm font-medium">
            <div class="h-10 w-16 bg-gray-300 rounded-md mb-2"></div>
            <span>Pas d’image</span>
          </div>
          <div class="min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
              {{ page.title }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Par <span class="font-medium text-gray-700">{{ page.author }}</span> • {{ this.utilsFront.formatDate(page.created * 1000) }}
            </p>
          </div>
        </a>
      </li>
    </ul>

      <!-- Pagination -->
      <nav class="flex items-center justify-center mt-8 space-x-2" aria-label="Pagination">
        <!-- Bouton précédent -->
        <a href="#" @click="this.changePage(1)"
           :class="this.getStylePagePagination()">
          <<
        </a>
        <a href="#" @click="this.changePage(this.page - 1)" :style=" this.page === 1 ? 'pointer-events: none' : ''"
           class=" px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition disabled:bg-amber-400 ">
          <
        </a>

        <!-- Numéros de page -->
        <a href="#"
           class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
          1
        </a>
        <a href="#"
           class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
          2
        </a>
        <a href="#"
           class="px-3 py-1 rounded-lg border border-indigo-500 bg-indigo-50 text-indigo-600 font-semibold transition">
          3
        </a>
        <a href="#"
           class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
          4
        </a>
        <a href="#"
           class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
          5
        </a>

        <!-- Bouton suivant -->
        <a href="#"
           class="px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
          >>
        </a>
      </nav>
  </div>

  <div v-else class="mx-auto w-full rounded-md p-4">
    <ul class="divide-y divide-gray-200">

      <li class="flex items-center gap-4 pb-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

      <li class="flex items-center gap-4 py-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

      <li class="flex items-center gap-4 py-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

      <li class="flex items-center gap-4 py-4">
        <div class="size-10 rounded-full bg-gray-200 animate-pulse"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-1/2 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-3 w-10/12 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </li>

    </ul>
  </div>
</template>
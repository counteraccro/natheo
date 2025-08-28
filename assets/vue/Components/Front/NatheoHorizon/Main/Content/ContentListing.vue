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
      limit: 20,
      page: 1,
      pages: '',
      nbElements: 0,
      title: '',
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
        this.title = datas.title;
        this.nbElements = datas.content.rows;
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

    /**
     * Génère le style des liens de la pagination
     * @param numberPage
     * @param isCentral
     * @param isEnd
     * @returns {string}
     */
    getStylePagePagination(numberPage, isCentral, isEnd) {

      if(numberPage === this.page && isCentral) {
        return "px-3 py-1 rounded-lg border border-gray-300 bg-theme-4-750 !text-theme-1-100 hover:dark:bg-gray-600 transition"
      }

      if(numberPage === this.getNbPage() && isEnd) {
        return "px-3 py-1 rounded-lg border border-gray-100 text-gray-300 transition disabled"
      }

      if(numberPage <= 1 && !isEnd && !isCentral) {
        return "px-3 py-1 rounded-lg border border-gray-100 text-gray-300 transition disabled"
      }

      return "px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-theme-4-750 hover:!text-theme-1-100 hover:dark:bg-gray-600 transition"
    },

    /**
     * Retourne le nombre de page
     * @returns {number}
     */
    getNbPage() {
      return Math.ceil(this.nbElements / this.limit);
    },

    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },
  }
}
</script>

<template>
  <h2 class="text-slate-900 text-2xl font-semibold mb-3"> {{ this.title }} </h2>
  <div v-if="this.isLoad && this.pages.length > 0">
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


      <nav class="content-paginate flex items-center justify-center mt-8 space-x-2" aria-label="Pagination">
        <a href="#" @click="this.changePage(1)" :class="this.getStylePagePagination(this.page, false, false)"><<</a>
        <a href="#" @click="this.changePage(this.page - 1)" :class="this.getStylePagePagination(this.page, false,false)"><</a>

        <a href="#" v-for="(n, i) in this.getNbPage()"
           :class="this.getStylePagePagination(n, true,false)" @click="this.changePage(n)">
          <span v-if="n === this.page-1 || n === this.page+1 || n === this.page || n <= 2 || n >= this.getNbPage()-1">{{ n }}</span>
          <span v-else-if="n === this.page-2 || n === this.page+2">...</span>
        </a>

        <a href="#" @click="this.changePage(this.page + 1)" :class="this.getStylePagePagination(this.page, false,true)">></a>
        <a href="#" @click="this.changePage(this.getNbPage())" :class="this.getStylePagePagination(this.page, false,true)">>></a>
      </nav>
      <div class="flex items-center justify-center mt-2 text-gray-400 text-sm">
         {{ this.page }} sur {{ this.getNbPage() }} - {{ this.nbElements }} page(s)
      </div>
  </div>

  <div v-else-if="this.isLoad && this.pages.length === 0">
    <div class="p-6">
      <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 p-8 text-center">
        <p class="text-gray-500 text-lg font-medium">Aucun élément trouvé</p>
      </div>
    </div>
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
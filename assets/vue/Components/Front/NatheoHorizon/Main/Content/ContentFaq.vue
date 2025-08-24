<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu de type listing
 */
export default {
  name: 'ContentFaq',
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
      faq: null,
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
        this.faq = datas
      }

      let loader = () => {
        this.isLoad = true
      }
      let params = {
        'id': this.data.id,
        'locale': this.locale
      };
      this.ajaxRequest.getContentPage(params, success, this.apiFailure, loader)
    },

    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },
  }
}
</script>

<template>
  <div v-if="this.isLoad">

    <div v-for="category in this.faq.content.categories" class="space-y-4">
      <h2 class="text-slate-900 text-2xl font-semibold mb-3 mt-3">{{ category.title }}</h2>
      <details v-for="question in category.questions" class="group [&_summary::-webkit-details-marker]:hidden">
        <summary class="flex items-center justify-between gap-1.5 rounded-md border border-gray-200 bg-white shadow-sm p-4 text-gray-900 group-open:bg-theme-4-750 group-open:!text-theme-1-100 group-open:dark:bg-gray-600 hover:bg-theme-4-750 hover:!text-theme-1-100 hover:cursor-pointer hover:dark:bg-gray-600">
          <h2 class="text-lg">{{ question.title }}</h2>
          <svg
              class="size-5 shrink-0 transition-transform duration-300 group-open:-rotate-180"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </summary>
        <p class="px-4 pt-4 text-slate-600">
          {{ question.answer }}
        </p>
      </details>
    </div>
  </div>
  <div v-else>
    <div class="space-y-6 p-4">
      <!-- Titre de la page -->
      <div class="h-8 w-1/4 rounded bg-gray-200 animate-pulse"></div>

      <!-- Bloc FAQ -->
      <div class="space-y-4">
        <!-- Question -->
        <div class="h-6 w-2/3 rounded bg-gray-200 animate-pulse"></div>
        <!-- Réponse -->
        <div class="space-y-2">
          <div class="h-4 w-full rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-5/6 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-2/3 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="h-6 w-1/2 rounded bg-gray-200 animate-pulse"></div>
        <div class="space-y-2">
          <div class="h-4 w-full rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-4/5 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-2/3 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </div>
    </div>
  </div>
</template>
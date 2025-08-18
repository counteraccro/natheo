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
        console.log(datas)
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
    content faq
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
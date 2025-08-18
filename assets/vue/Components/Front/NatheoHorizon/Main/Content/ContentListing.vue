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
    content listing
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
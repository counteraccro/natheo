<script>
import { marked } from 'marked';

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu de type texte
 */
export default {
  name: 'ContentText',
  props: {
    data: Object,
    utilsFront: Object,
    locale: String,
    ajaxRequest: Object,
  },
  emits: ['api-failure'],
  data() {
    return {
      content: '',
      isLoad: false,
    };
  },
  created() {
    this.loadContent();
  },
  mounted() {
    /**
     * Ajoute un span à la fin de chaque ligne
     */
    document.querySelectorAll('pre code').forEach((codeEl) => {
      if (codeEl.dataset.lnProcessed === '1') return;

      if (codeEl.querySelector('.code-line')) {
        codeEl.dataset.lnProcessed = '1';
        return;
      }

      const html = codeEl.innerHTML;
      let lines = html.split(/\r?\n/);
      if (lines.length && lines[lines.length - 1].trim() === '') lines.pop();

      const wrapped = lines.map((line) => `<span class="code-line">${line.length ? line : '&nbsp;'}</span>`).join('');
      codeEl.innerHTML = wrapped;
      codeEl.dataset.lnProcessed = '1';
    });
  },
  computed: {
    output() {
      return marked(this.content);
    },
  },
  methods: {
    loadContent() {
      let success = (datas) => {
        this.content = datas.content;
      };

      let loader = () => {
        this.isLoad = true;
      };
      let params = {
        id: this.data.id,
        locale: this.locale,
      };
      this.ajaxRequest.getContentPage(params, success, this.apiFailure, loader);
    },

    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },
  },
};
</script>

<template>
  <div v-if="this.isLoad" class="natheo-content-text" v-html="this.output"></div>
  <div v-else>
    <div class="mx-auto w-full rounded-md p-4">
      <div class="flex flex-col sm:flex-row animate-pulse gap-4">
        <div class="size-10 rounded-full bg-gray-200 mx-auto sm:mx-0"></div>
        <div class="flex-1 space-y-6 py-1">
          <div class="h-2 rounded bg-gray-200"></div>

          <div class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="sm:col-span-2 h-2 rounded bg-gray-200"></div>
              <div class="sm:col-span-1 h-2 rounded bg-gray-200"></div>
            </div>

            <div class="h-2 rounded bg-gray-200"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>

            <div class="mt-6"></div>
            <div class="h-4 bg-gray-200 rounded w-full mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2 w-full"></div>

            <div class="mt-6"></div>
            <div class="h-4 bg-gray-200 rounded w-full mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2 w-full"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

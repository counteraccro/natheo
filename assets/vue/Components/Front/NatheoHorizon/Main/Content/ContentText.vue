<script>
import {marked} from 'marked'

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu de type texte
 */
export default {
  name: 'ContentText',
  props: {
    data: String,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

    /**
     * Ajoute un span à la fin de chaque ligne
     */
    document.querySelectorAll("pre code").forEach(codeEl => {
      if (codeEl.dataset.lnProcessed === "1") return;

      if (codeEl.querySelector(".code-line")) {
        codeEl.dataset.lnProcessed = "1";
        return;
      }

      const html = codeEl.innerHTML;
      let lines = html.split(/\r?\n/);
      if (lines.length && lines[lines.length - 1].trim() === "") lines.pop();

      const wrapped = lines
          .map(line => `<span class="code-line">${line.length ? line : "&nbsp;"}</span>`).join("");
      codeEl.innerHTML = wrapped;
      codeEl.dataset.lnProcessed = "1";
    });
  },
  computed: {
    output() {
      return marked(this.data);
    },
  },
  methods: {

  }
}
</script>

<template>
  <div class="natheo-content-text" v-html="this.output"></div>
</template>
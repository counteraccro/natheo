<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de transformer un texte après un double click dessus en un champ éditable
 */
import axios from "axios";

export default {
  name: "FieldEditor",
  components: {},
  props: {
    value: String,
    balise: String,
    id: String
  },
  data() {
    return {}
  },
  mounted() {

  },
  methods: {

    /**
     * Génère la balise html, si end = true, génère une balise fermente
     * @param end
     */
    getBalise(end = false) {
      let balise = '<' + this.balise + ' id="' + this.id + '">';
      if (end) {
        balise = '</' + this.balise + '>';
      }
      return balise;
    },

    /**
     * Construit la valeur à afficher avec la balise
     * @returns {string}
     */
    getFormatedValue() {
      return this.getBalise() + this.value + this.getBalise(true);
    },
  }
}
</script>

<template>
  <div v-html="this.getFormatedValue()">
  </div>
</template>
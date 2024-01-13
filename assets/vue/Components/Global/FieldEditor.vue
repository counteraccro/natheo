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
    return {
      editMode: false
    }
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

    switchMode() {
      alert('oki');
    },

    /**
     * Construit la valeur à afficher avec la balise
     * @returns {string}
     */
    getTextValue() {
      return this.getBalise() + this.value + this.getBalise(true);
    },
  }
}
</script>

<template>
    <div v-if="!editMode" class="clearfix">
      <div class="float-start me-2" v-html="this.getTextValue()"></div>
      <span class="btn btn-sm btn-secondary" style=" --bs-btn-font-size: .75rem;" @click="this.switchMode()"><i class="bi bi-pencil-fill"></i></span>
    </div>

</template>
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
    pValue: String,
    balise: String,
    id: String
  },
  data() {
    return {
      editMode: false,
      value: this.pValue,
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

    /**
     * Change le mode entre édition et lecture
     * @param action
     */
    switchMode(action = 'edit') {
      if (action === 'edit') {
        this.editMode = true;
      } else {
        this.editMode = false;
      }
    },

    /**
     * Construit la valeur à afficher avec la balise
     * @returns {string}
     */
    getTextValue() {
      return this.getBalise() + this.value + this.getBalise(true);
    },

    save() {
      this.value = document.getElementById('field-editor-input-' + this.id).value;
      this.switchMode('see');
    }
  }
}
</script>

<template>

  <div class="clearfix">
    <div v-if="!editMode">
      <div class="float-start me-2" v-html="this.getTextValue()"></div>
      <span class="btn btn-sm btn-secondary" style=" --bs-btn-font-size: .75rem;" @click="this.switchMode()"><i class="bi bi-pencil-fill"></i></span>
    </div>
    <div v-else>
      <div class="input-group">
        <input type="text" class="form-control" :id="'field-editor-input-' + this.id" :value="this.value">
        <button class="btn btn-secondary" type="button" @click="this.save"><i class="bi bi-check-circle-fill"></i> </button>
        <button class="btn btn-secondary" type="button" @click="this.switchMode('read')"><i class="bi bi-x-circle-fill"></i></button>
      </div>
    </div>
  </div>

</template>
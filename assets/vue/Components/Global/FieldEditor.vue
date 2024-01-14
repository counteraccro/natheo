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
    id: String,
    rule: {
      type: String,
      default: "",
    },
    ruleMsg: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      editMode: false,
      value: this.pValue,
      errorClass: ""
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

    /**
     * Vérifie la règle de validation si elle est défini
     * @returns {boolean}
     */
    checkRule(value) {
      /*if (this.ruleRegex === "") {
        return true;
      }

      console.log(value);
      console.log(!value.match(this.ruleRegex));

      return value.match(this.ruleRegex);*/
    },

    save() {
      let tmp = document.getElementById('field-editor-input-' + this.id).value;
      if(this.checkRule(tmp)) {
        this.errorClass = "";
        this.value = tmp;
        this.switchMode('see');
      }
      else {
        this.errorClass = "is-invalid";
      }
    },
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
        <input type="text" class="form-control" :class="this.errorClass" :id="'field-editor-input-' + this.id" :value="this.value">
        <button class="btn btn-secondary" type="button" @click="this.save"><i class="bi bi-check-circle-fill"></i>
        </button>
        <button class="btn btn-secondary" type="button" @click="this.switchMode('read')">
          <i class="bi bi-x-circle-fill"></i></button>
        <div class="invalid-feedback">
          You must agree before submitting.
        </div>
      </div>
    </div>
  </div>

</template>
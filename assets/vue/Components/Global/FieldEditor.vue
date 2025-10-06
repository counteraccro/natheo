<script>
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Permet de transformer un texte après un double click dessus en un champ éditable
 */

export default {
  name: 'FieldEditor',
  components: {},
  emit: ['get-value'],
  props: {
    pValue: String,
    balise: String,
    id: String,
    rule: {
      type: String,
      default: '',
    },
    ruleMsg: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      editMode: false,
      value: this.pValue,
      errorClass: '',
    };
  },
  mounted() {},
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
      this.errorClass = '';
      if (action === 'edit') {
        this.editMode = true;
      } else if (action === 'reset') {
        this.value = this.pValue;
        this.editMode = false;
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
      let bReturn = true;
      switch (this.rule) {
        case 'isEmpty':
          bReturn = value.trim() !== '';
          break;
        default:
          bReturn = true;
      }
      return bReturn;
    },

    /**
     * Permet de renvoyer
     */
    getValue() {
      if (this.checkRule(this.value)) {
        this.errorClass = '';
        this.switchMode('see');
        this.$emit('get-value', this.value, this.id);
      } else {
        this.errorClass = 'is-invalid';
      }
    },
  },
};
</script>

<template>
  <div class="clearfix">
    <div v-if="!editMode">
      <div class="float-start me-2" v-html="this.getTextValue()"></div>
      <span class="btn btn-sm btn-secondary" style="--bs-btn-font-size: 0.75rem" @click="this.switchMode()"
        ><i class="bi bi-pencil-fill"></i
      ></span>
    </div>
    <div v-else>
      <div class="input-group mb-2">
        <input
          type="text"
          class="form-control"
          :class="this.errorClass"
          :id="'field-editor-input-' + this.id"
          v-model="this.value"
        />
        <button class="btn btn-outline-secondary" type="button" @click="this.getValue">
          <i class="bi bi-check-circle"></i>
        </button>
        <button class="btn btn-outline-secondary" type="button" @click="this.switchMode('reset')">
          <i class="bi bi-arrow-counterclockwise"></i>
        </button>
      </div>
      <div class="text-danger" v-if="this.errorClass === 'is-invalid'" v-html="this.ruleMsg"></div>
    </div>
  </div>
</template>

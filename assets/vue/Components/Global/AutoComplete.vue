<script>

/**
 * Champ avec autoCompletion de résultat
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import {debounce} from "../../../utils/debouce";

export default {
  name: 'AutoComplete',
  props: {
    url: String,
    locale: String,
    translate: Object,
  },
  emits: ['select-value'],
  data() {
    return {
      results: [],
      id: 0,
      value: '',
      debounceFn: null,
      loading: false,
      showResult: false,
      nbResult: 0,
    }
  },
  mounted() {
    this.id = this.getId(5);
    this.debounceFn = debounce(() => this.search(), 800)
  },
  computed: {},
  methods: {


    /**
     * Génère un int de taille size
     * @param size
     */
    getId(size) {
      let id = '';
      for (let i = 0; i < size; i++) {
        id += Math.floor(Math.random() * 9);
      }
      return id;
    },

    /**
     * Évènement onKeyUp
     */
    onKeyup() {
      this.debounceFn();
    },

    /**
     * Ajoute un element
     */
    addElement() {
      this.$emit('select-value', this.value);
    },

    /**
     * Évènement click sur le bouton
     */
    onClick(value) {
      this.value = '';
      this.showResult = false;
      this.$emit('select-value', value);
      return false;
    },

    /**
     * Masque le résultat
     */
    hideResult() {
      this.showResult = false;
    },

    /**
     * Appel ajax pour construire le résultat d'auto-completion
     */
    search() {
      if (this.value === '') {
        return false;
      }
      this.loading = true;
      axios.get(this.url + '/' + this.value + '/' + this.locale, {}).then((response) => {
        this.results = response.data.result;
        this.nbResult = Object.keys(this.results).length;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.showResult = true;
        this.loading = false;
      });
    }

  }
}

</script>

<template>

  <div class="mb-3">
    <label :for="'auto-complete-input-' + this.id" class="form-label">{{ this.translate.auto_complete_label }}</label>

    <div class="input-group mb-3">
      <input class="form-control" v-model="this.value"
          @keyup="this.onKeyup()" :id="'auto-complete-input-' +this.id"
          :placeholder="this.translate.auto_complete_placeholder">
      <button class="btn btn-secondary" type="button" @click="this.addElement" :disabled="this.nbResult !== 0">
        <span v-if="!this.loading" v-html="this.translate.auto_complete_btn"></span>
        <span v-else v-html="this.translate.auto_complete_btn_loading"></span>
      </button>
      <ul v-if="this.showResult" class="dropdown-menu auto-complete-result" style="display: block">
        <li v-if="this.nbResult > 0" v-for="{data, label} in this.results">
          <a class="dropdown-item no-control" href="#" @click="this.onClick(data)" v-html="label"></a>
        </li>
        <li v-else>
          <a class="dropdown-item no-control" href="#" @click="this.hideResult">{{ this.translate.auto_complete_empty_result }}</a>
        </li>
      </ul>
    </div>
    <div id="emailHelp" class="form-text">{{ this.translate.auto_complete_help }}</div>
  </div>

</template>
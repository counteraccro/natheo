<script>

/**
 * Champ avec autoCompletion de résultat
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import {isEmpty} from "lodash-es";
import {debounce} from "../../../utils/debouce";

export default {
  name: 'AutoComplete',
  props: {
    url: String,
    locale: String,
    translate: Object,
  },
  emits: [''],
  data() {
    return {
      results: [],
      id: 0,
      value: '',
      debounceFn: null
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
    onKeyup()
    {
      this.debounceFn();
    },

    /**
     * Appel ajax pour construire le résultat d'auto-completion
     */
    search() {
      axios.post(this.url, {
        'search': this.value,
        'locale': this.locale
      }).then((response) => {
        this.results = response.data.result
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });
    }

  }
}

</script>

<template>

  <div class="mb-3">
    <label :for="'auto-complete-input-' + this.id" class="form-label">{{ this.translate.auto_complete_label }}</label>
    <input :list="'data-list-option-' + this.id" autocomplete="off" class="form-control" v-model="this.value"
           @keyup="this.onKeyup()" :id="'auto-complete-input-' +this.id"
           :placeholder="this.translate.auto_complete_placeholder">
    <div id="emailHelp" class="form-text">{{ this.translate.auto_complete_help }}</div>
    <datalist :id="'data-list-option-' + this.id">
      <select>
        <option v-for="data in this.results" :value="data"  />
      </select>
    </datalist>
  </div>

</template>
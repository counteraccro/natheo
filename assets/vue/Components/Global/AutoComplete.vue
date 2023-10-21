<script>

/**
 * Champ avec autoCompletion de résultat
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import {isEmpty} from "lodash-es";

export default {
  name: 'AutoComplete',
  props: {
    url: String,
    translate: Object,
  },
  emits: [''],
  data() {
    return {
      results: [],
      id: 0,
    }
  },
  mounted() {
    this.id = this.getId(5);
  },
  computed: {},
  methods: {


    /**
     * Génère un int de taille size
     * @param size
     */
    getId(size)
    {
      let id = '';
      for (let i = 0; i < size; i++) {
        id += Math.floor(Math.random() * 9);
        console.log(id);
      }
      return id;
    },

    loadData() {
      axios.post(this.url, {
        //'page': page
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
    <input type="email" class="form-control" :id="'auto-complete-input-' +this.id" :placeholder="this.translate.auto_complete_placeholder">
    <div id="emailHelp" class="form-text">{{ this.translate.auto_complete_help }}</div>
  </div>

</template>
<script>

/**
* Ajout de contenu dans la page
* @author Gourdon Aymeric
* @version 1.0
*/
import axios from "axios";
import {debounce} from "../../../utils/debouce";

export default {
  name: 'PageContent',
  props: {
    url: String,
    locale: String,
    translate: Object,
    page: Object
  },
  emits: ['add-content'],
  data() {
    return {

    }
  },
  mounted() {

  },
  computed: {},
  methods: {


    /**
     * Évènement click sur le bouton
     */
    onClick() {
      //this.$emit('select-value', this.value);
    },

    /**
     * Appel ajax pour construire le résultat d'auto-completion
     */
    search() {
      /*axios.post(this.url, {
        'locale': this.locale
      }).then((response) => {
        this.results = response.data.result
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });*/
    }

  }
}

</script>

<template>

  <h5>{{ this.translate.title }}</h5>

  <div v-if="this.page.render === 1">
    1 block
  </div>
  <div v-else-if="this.page.render === 2">
    2 block
  </div>
  <div v-else-if="this.page.render === 3">
    3 block
  </div>
  <div v-else-if="this.page.render === 4">
    2 block + 1 blocs
  </div>
  <div v-else-if="this.page.render === 5">
    2 block + 2 block
  </div>
</template>

<style scoped>

</style>
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

    getNbRow()
    {
      switch (this.page.render) {
        case 1:
          return 1;
        case 2:
          return 2;
        case 3:
          return 3;
      }
    },

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

  <div v-if="this.page.render < 4">
    1 block
    <div class="row">
      <div v-for="n in this.getNbRow()" :class="'col-' + (12/this.getNbRow())">
        <div v-for="pageContent in this.page.pageContents">

          <div v-for="pCT in pageContent.pageContentTranslations">
            <div v-if="pCT.locale === this.locale && pageContent.renderBlock === n">
              {{ pCT.text }}
            </div>
          </div>
        </div>
      </div>
    </div>
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
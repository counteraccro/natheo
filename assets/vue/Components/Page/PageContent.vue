<script>

/**
 * Ajout de contenu dans la page
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import {debounce} from "../../../utils/debouce";
import {marked} from "marked";

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
      isEmptyBlock: true,
    }
  },
  mounted() {

  },
  computed: {},
  methods: {
    marked,

    getNbRow() {
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
    <div class="row">
      <div v-for="n in this.getNbRow()" :set="this.isEmptyBlock = false" :class="'col-' + (12/this.getNbRow())">
        <div v-for="pageContent in this.page.pageContents">
          <div v-for="pCT in pageContent.pageContentTranslations">
            <div v-if="pCT.locale === this.locale && pageContent.renderBlock === n">
              <div v-html="marked(pCT.text)" :set="this.isEmptyBlock = true"></div>
            </div>
          </div>
        </div>

        <div v-if="!this.isEmptyBlock">
          Block vide
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
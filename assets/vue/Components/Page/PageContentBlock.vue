<script>/**
 * Block de rendu pour le content
 * @author Gourdon Aymeric
 * @version 1.0
 */

import {marked} from "marked";

export default {
  name: 'PageContentBlock',
  props: {
    locale: String,
    translate: Object,
    pageContents: Object,
    renderBlockId: Number
  },
  data() {
    return {
      isEmptyBlock: false,
    }
  },
  mounted() {

  },
  computed: {},
  methods: {
    marked,

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
  <div :set="this.isEmptyBlock = false">
    <div v-for="pageContent in this.pageContents">
      <div v-for="pCT in pageContent.pageContentTranslations">
        <div v-if="pCT.locale === this.locale && pageContent.renderBlock === this.renderBlockId">
          <div v-html="marked(pCT.text)" :set="this.isEmptyBlock = true"></div>
        </div>
      </div>
    </div>

    <div v-if="!this.isEmptyBlock" class="mb4 mt-4">
      Block vide
    </div>
  </div>
</template>

<style scoped>

</style>
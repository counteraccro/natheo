<script>

/**
 * Ajout de contenu dans la page
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import PageContentBlock from "./PageContentBlock.vue";

export default {
  name: 'PageContent',
  components: {
    PageContentBlock
  },
  props: {
    url: String,
    locale: String,
    translate: Object,
    page: Object
  },
  emits: ['add-content'],
  data() {
    return {
      renderColumn: [1, 2, 3],
      renderRow: [4, 5]
    }
  },
  mounted() {

  },
  computed: {},
  methods: {

    /**
     * Retourne le nombre d'itérations en fonction du render
     * @returns {number}
     */
    getNbIteration() {
      switch (this.page.render) {
        case 1:
          return 1;
        case 2:
          return 2;
        case 3:
          return 3;
        case 4:
          return 2;
        case 5:
          return 3;
      }
    },

    /**
     * La valeur existe dans la liste
     * @param list
     * @param number
     * @returns boolean
     */
    inArray(list, number) {
      return list.includes(number)
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

  <div v-if="this.inArray(this.renderColumn, this.page.render)">
    <div class="row">
      <div v-for="n in this.getNbIteration()" :class="'col-' + (12/this.getNbIteration())">
        <page-content-block
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="n"
        />
      </div>
    </div>
  </div>

  <div v-if="this.inArray(this.renderRow, this.page.render)">
    <div class="row">
      <div v-for="n in this.getNbIteration()" class="col-12">
        <page-content-block
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="n"
        />
      </div>
    </div>
  </div>

  <div v-else-if="this.page.render === 6">
    2 block + 1 blocs
  </div>
  <div v-else-if="this.page.render === 7">
    2 block + 2 block
  </div>
</template>

<style scoped>

</style>
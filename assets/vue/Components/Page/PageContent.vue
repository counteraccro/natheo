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
  emits: ['auto-save', 'remove-content'],
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
        case 6:
          return 2;
        case 7:
          return 2;
        case 8:
          return 2;
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
     * retourne un nombre aléatoire
     * @returns {string}
     */
    randomKey()
    {
      return (Math.random() + 1).toString(36).substring(7);
    },

    /**
     * Évènement click sur le bouton
     */
    autoSave() {
      this.$emit('auto-save', this.page);
    },

    /**
     * suppression d'un page content en fonction de son id
     * @param id
     */
    removeContent(id)
    {
      this.$emit('remove-content', id);
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
            :index-start="0"
            :index-end="2"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"

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
            :index-start="0"
            :index-end="2"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>
  </div>

  <div v-else-if="this.page.render === 6">
    <div class="row mb-4">
      <div class="col-12">
        <page-content-block :key="this.randomKey()"
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="1"
            :index-start="0"
            :index-end="0"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>

    <div class="row">
      <div v-for="n in this.getNbIteration()" class="col-6">
        <page-content-block :key="this.randomKey()"
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="n+1"
            :index-start="1"
            :index-end="2"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>

  </div>
  <div v-else-if="this.page.render === 7">
    <div class="row mb-4">
      <div v-for="n in this.getNbIteration()" class="col-6">
        <page-content-block :key="this.randomKey()"
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="n"
            :index-start="0"
            :index-end="1"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <page-content-block :key="this.randomKey()"
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="3"
            :index-start="3"
            :index-end="3"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>

  </div>
  <div v-else-if="this.page.render === 8">
    <div class="row mb-4">
      <div v-for="n in this.getNbIteration()" class="col-6">
        <page-content-block :key="this.randomKey()"
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="n"
            :index-start="0"
            :index-end="1"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>
    <div class="row">
      <div v-for="n in this.getNbIteration()" class="col-6">
        <page-content-block :key="this.randomKey()"
            :translate="this.translate.page_content_block"
            :locale="this.locale"
            :page-contents="this.page.pageContents"
            :render-block-id="n+2"
            :index-start="2"
            :index-end="3"
            @auto-save="this.autoSave"
            @remove-content="this.removeContent"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>
<script>/**
 * Block de rendu pour le content
 * @author Gourdon Aymeric
 * @version 1.0
 */

import {marked} from "marked";
import MarkdownEditor from "../Global/MarkdownEditor.vue";

export default {
  name: 'PageContentBlock',
  components: {MarkdownEditor},
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

    updatePageContentText(id, value)
    {
      console.log('id : ' + id);
      console.log(value);
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
  <div :set="this.isEmptyBlock = false">
    <div v-for="pageContent in this.pageContents">
      <div v-if="pageContent.typeId === null">
        <div v-for="pCT in pageContent.pageContentTranslations">
          <div v-if="pCT.locale === this.locale && pageContent.renderBlock === this.renderBlockId">
            <div :set="this.isEmptyBlock = true"></div>
            <markdown-editor :key="pCT.id"
                :me-id="pCT.id"
                :me-value="pCT.text"
                :me-rows="10"
                :me-translate="this.translate.markdown"
                :me-key-words="{}"
                :me-save="true"
                :me-preview="false"
                @editor-value="this.updatePageContentText"
                @editor-value-change="this.updatePageContentText"
            >
            </markdown-editor>

          </div>
        </div>
      </div>
      <div v-else-if="pageContent.renderBlock === this.renderBlockId" :set="this.isEmptyBlock = true">
        block type {{ pageContent.typeId}}
      </div>
    </div>

    <div v-if="!this.isEmptyBlock" class="mb4 mt-4">
      Block vide
    </div>
  </div>
</template>

<style scoped>

</style>
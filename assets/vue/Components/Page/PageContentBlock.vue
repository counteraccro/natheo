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
  emits: ['update-text-content'],
  props: {
    locale: String,
    translate: Object,
    pageContents: Object,
    renderBlockId: Number,
    indexStart: Number,
    indexEnd: Number,
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
     * Met à jour le texte d'un content text en fonction de son id
     * @param id
     * @param value
     */
    updatePageContentText(id, value) {

      this.pageContents.forEach((pC) => {
            if(pC.typeId === null) {

              pC.pageContentTranslations.forEach((pCT) => {
                if (pCT.id === id) {
                  pCT.text = value;
                }
              })
            }
          }
      );
      this.$emit('update-text-content');
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
    <div v-for="(pageContent, index) in this.pageContents">
      <div v-if="this.indexStart >= index || index <= this.indexEnd ">
        <div v-if="pageContent.typeId === null">
          <div v-for="pCT in pageContent.pageContentTranslations">
            <div v-if="pCT.locale === this.locale && pageContent.renderBlock === this.renderBlockId">
              <div :set="this.isEmptyBlock = true"></div>
              <markdown-editor :key="pCT.id"
                  :me-id="pCT.id"
                  :me-value="pCT.text"
                  :me-rows="10"
                  :me-translate="this.translate.markdown"
                  :me-key-words="[]"
                  :me-save="true"
                  :me-preview="false"
                  @editor-value="this.updatePageContentText"
                  @editor-value-change=""
              >
              </markdown-editor>

            </div>
          </div>
        </div>
        <div v-else-if="pageContent.renderBlock === this.renderBlockId" :set="this.isEmptyBlock = true">
          block type {{ pageContent.typeId }}
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
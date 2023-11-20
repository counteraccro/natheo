<script xmlns="http://www.w3.org/1999/html">/**
 * Block de rendu pour le content
 * @author Gourdon Aymeric
 * @version 1.0
 */

import {marked} from "marked";
import MarkdownEditor from "../Global/MarkdownEditor.vue";
import {Modal} from "bootstrap";

export default {
  name: 'PageContentBlock',
  components: {MarkdownEditor},
  emits: ['auto-save', 'remove-content', 'new-content'],
  props: {
    locale: String,
    translate: Object,
    pageContents: Object,
    renderBlockId: Number,
    indexStart: Number,
    indexEnd: Number,
    listeContent: Object
  },
  data() {
    return {
      isEmptyBlock: false,
      idConfirm: 0,
      modalRemove: null,
      modalNew: null,
      idSelectContent: 0,
    }
  },
  mounted() {
    this.modalRemove = new Modal(document.getElementById("modal-remove-content-" + this.renderBlockId), {});
    this.modalNew = new Modal(document.getElementById("modal-new-content-" + this.renderBlockId), {});
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
            if (pC.typeId === null) {

              pC.pageContentTranslations.forEach((pCT) => {
                if (pCT.id === id) {
                  pCT.text = value;
                }
              })
            }
          }
      );
      this.$emit('auto-save');
    },

    /**
     * Permet de supprimer une page content
     * @param id
     * @param confirm
     */
    removeContent(id, confirm) {
      if (!confirm) {
        this.idConfirm = id;
        this.showModal(this.modalRemove)
      } else {
        this.hideModal(this.modalRemove);
        this.$emit('remove-content', id);
      }

    },

    /**
     * AJoute un nouveau content
     * @param id
     */
    newContent()
    {
      if(this.idSelectContent > 0)
      {
        this.$emit('new-content', this.idSelectContent, this.renderBlockId);
        this.hideModal(this.modalNew);
      }

    },

    /**
     * Affiche les objets modales
     * @param modale
     */
    showModal(modale) {
      modale.show();
    },

    /**
     * Ferme les objets modales
     * @param modale
     */
    hideModal(modale) {
      modale.hide();
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
              <div class="block-page-content">
                <markdown-editor :key="pCT.id"
                    :me-id="pCT.id"
                    :me-value="pCT.text"
                    :me-rows="14"
                    :me-translate="this.translate.markdown"
                    :me-key-words="[]"
                    :me-save="true"
                    :me-preview="false"
                    @editor-value="this.updatePageContentText"
                    @editor-value-change=""
                >
                </markdown-editor>

                <div class="block-btn mt-4">
                  <div class="btn btn-secondary me-2">
                    <i class="bi bi-arrows-move"></i>
                    {{ this.translate.btn_change_content }}
                  </div>
                  <div class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left-right"></i>
                    {{ this.translate.btn_move_content }}
                  </div>
                  <div class="btn btn-danger" @click="this.removeContent(pageContent.id, false)">
                    <i class="bi bi-x-circle"></i>
                    {{ this.translate.btn_delete_content }}
                  </div>
                </div>

              </div>

            </div>
          </div>
        </div>
        <div v-else-if="pageContent.renderBlock === this.renderBlockId" :set="this.isEmptyBlock = true">
          <div class="block-page-content">
            block type {{ pageContent.typeId }}
          </div>
        </div>
      </div>
    </div>

    <div v-if="!this.isEmptyBlock">
      <div class="block-page-content position-relative">
        <div class="btn btn-secondary position-absolute top-50 start-50 translate-middle" @click="this.showModal(this.modalNew)">
          <i class="bi bi-plus-circle"></i>
          {{ this.translate.btn_new_content }}
        </div>
      </div>
    </div>
  </div>


  <!-- Confirmation modale suppression content -->
  <div class="modal fade" :id="'modal-remove-content-' + this.renderBlockId" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi bi-trash-fill"></i> {{ this.translate.modale_remove_title }}
          </h1>
          <button @click="this.hideModal(this.modalRemove)" type="button" class="btn-close" data-bs-dismiss="modal"
              aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {{ this.translate.modale_remove_body }} <br />
          <span class="text-info"><i> {{ this.translate.modale_remove_body_2 }}</i></span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="this.hideModal(this.modalRemove)" data-bs-dismiss="modal">
            {{ this.translate.modale_remove_btn_cancel }}
          </button>
          <button type="button" class="btn btn-primary"
              @click="this.removeContent(this.idConfirm, true)">
            {{ this.translate.modale_remove_btn_confirm }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modale d'ajout d'un nouveau content  -->
  <div class="modal fade" :id="'modal-new-content-' + this.renderBlockId" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi bi-plus-circle"></i> {{ this.translate.modale_new_title }}
          </h1>
          <button @click="this.hideModal(this.modalNew)" type="button" class="btn-close" data-bs-dismiss="modal"
              aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label for="list-choice-content" class="form-label">{{ this.translate.modale_new_choice_label }}</label>
            <select id="list-choice-content" class="form-select" v-model="this.idSelectContent">
              <option value="0">---</option>
              <option v-for="(value, key) in this.listeContent" :value="parseInt(key)">{{ value }}</option>
            </select>
            <div id="list-status-help" class="form-text">{{ this.translate.modale_new_choice_info }}</div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="this.hideModal(this.modalNew)" data-bs-dismiss="modal">
            {{ this.translate.modale_new_btn_cancel }}
          </button>
          <button type="button" class="btn btn-primary" @click="this.newContent()">
            {{ this.translate.modale_new_btn_new }}
          </button>
        </div>
      </div>
    </div>
  </div>


</template>

<style scoped>

</style>
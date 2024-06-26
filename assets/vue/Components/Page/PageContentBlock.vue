<script xmlns="http://www.w3.org/1999/html">/**
 * Block de rendu pour le content
 * @author Gourdon Aymeric
 * @version 1.0
 */

import {marked} from "marked";
import MarkdownEditor from "../Global/MarkdownEditor.vue";
import {Modal} from "bootstrap";
import axios from "axios";

export default {
  name: 'PageContentBlock',
  components: {MarkdownEditor},
  emits: ['remove-content', 'new-content', 'update-content-text', 'move-content'],
  props: {
    locale: String,
    translate: Object,
    pageContents: Object,
    renderBlockId: Number,
    indexStart: Number,
    indexEnd: Number,
    indexMax: Number,
    listeContent: Object,
    url: String,
    urlInfo: String
  },
  data() {
    return {
      isEmptyBlock: false,
      idConfirm: 0,
      modalRemove: null,
      modalNew: null,
      idSelectContent: 0,
      idSelectTypeContent: 0,
      infoBlock_1: 'Chargement',
      infoBlock_2: 'Chargement',
      infoBlock_3: 'Chargement',
      infoBlock_4: 'Chargement',
      typeContent: {
        list: [],
        label: '',
        help: ''
      },
      loading: false
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
      this.$emit('update-content-text', id, value);
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
    newContent() {
      if (this.idSelectContent <= 0) {
        return false;
      }

      this.$emit('new-content', this.idSelectContent, this.idSelectTypeContent, this.renderBlockId);
      this.hideModal(this.modalNew);
      this.resetDataNewContent();
    },

    /**
     * Change le renderBlock en plus ou moins
     */
    moveContent(signe, renderBlockId) {
      this.$emit('move-content', signe, renderBlockId);
    },

    /**
     * Charge une liste de type de content en fonction de son id
     */
    loadListContentType() {
      if (this.idSelectContent <= 1) {
        this.typeContent.list = [];
        this.idSelectTypeContent = 0;
        return false;
      }

      this.loading = true;

      axios.get(this.url + '/' + this.idSelectContent, {})
          .then((response) => {
            this.typeContent.list = response.data.list;
            this.typeContent.label = response.data.label;
            this.typeContent.help = response.data.help;
            this.idSelectTypeContent = response.data.selected;
          }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Reset les données de la modale new content
     */
    resetDataNewContent() {
      this.idSelectContent = 0;
      this.typeContent.list = [];
      this.idSelectTypeContent = 0;
    },

    /**
     * Retourne les informations d'un content block et le set dans une variable infoBlock_
     * hack pour simuler un appel ajax, pas propre à changer à terme
     * @param type
     * @param typeId
     * @param renderBlock
     */
    getInfoRenderBlockByType(type, typeId, renderBlock)
    {
      axios.get(this.urlInfo + '/' + type + '/' + typeId, {})
          .then((response) => {
            let value = response.data.type + '<br />' + response.data.info;
            this.changeVal('infoBlock_' + renderBlock, value);

          }).catch((error) => {
        console.error(error);
      }).finally(() => {});
      return 'text-center';
    },

    /**
     * Affiche les objets modals
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

    changeVal(varName, newValue) {
      this[varName] = newValue;
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
                    :me-id="pageContent.renderBlock + '-' + pCT.locale"
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
                  <div class="float-start">
                    <div v-if="this.renderBlockId === 1">
                      <div v-if="this.indexMax !== 1" class="btn btn-sm btn-secondary me-2" @click="this.moveContent('+', this.renderBlockId)">
                        {{ this.translate.btn_move_content }}
                        <i class="bi bi-arrow-right"></i>
                      </div>
                    </div>
                    <div v-else-if="this.renderBlockId === this.indexMax">
                      <div class="btn btn-sm btn-secondary me-2" @click="this.moveContent('-', this.renderBlockId)">
                        <i class="bi bi-arrow-left"></i>
                        {{ this.translate.btn_move_content }}
                      </div>
                    </div>
                    <div v-else>
                      <div class="btn btn-sm btn-secondary me-2" @click="this.moveContent('-', this.renderBlockId)">
                        <i class="bi bi-arrow-left"></i>
                        {{ this.translate.btn_move_content }}
                      </div>
                      <div class="btn btn-sm btn-secondary me-2" @click="this.moveContent('+', this.renderBlockId)">
                        {{ this.translate.btn_move_content }}
                        <i class="bi bi-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                  <div class="btn btn-sm btn-danger" @click="this.removeContent(this.renderBlockId, false)">
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
            <div class="render-block-info" :class="this.getInfoRenderBlockByType(pageContent.type, pageContent.typeId, pageContent.renderBlock)">

            <div v-if="pageContent.renderBlock === 1" v-html="this.infoBlock_1"></div>
            <div v-if="pageContent.renderBlock === 2" v-html="this.infoBlock_2"></div>
            <div v-if="pageContent.renderBlock === 3" v-html="this.infoBlock_3"></div>
            <div v-if="pageContent.renderBlock === 4" v-html="this.infoBlock_4"></div>
            </div>

            <div class="block-btn mt-4">
              <div class="float-start">
                <div v-if="this.renderBlockId === 1">
                  <div v-if="this.indexMax !== 1" class="btn btn-sm btn-secondary me-2" @click="this.moveContent('+', this.renderBlockId)">
                    {{ this.translate.btn_move_content }}
                    <i class="bi bi-arrow-right"></i>
                  </div>
                </div>
                <div v-else-if="this.renderBlockId === this.indexMax">
                  <div class="btn btn-sm btn-secondary me-2" @click="this.moveContent('-', this.renderBlockId)">
                    <i class="bi bi-arrow-left"></i>
                    {{ this.translate.btn_move_content }}
                  </div>
                </div>
                <div v-else>
                  <div class="btn btn-sm btn-secondary me-2" @click="this.moveContent('-', this.renderBlockId)">
                    <i class="bi bi-arrow-left"></i>
                    {{ this.translate.btn_move_content }}
                  </div>
                  <div class="btn btn-sm btn-secondary me-2" @click="this.moveContent('+', this.renderBlockId)">
                    {{ this.translate.btn_move_content }}
                    <i class="bi bi-arrow-right"></i>
                  </div>
                </div>
              </div>
              <div class="btn btn-sm btn-danger" @click="this.removeContent(this.renderBlockId, false)">
                <i class="bi bi-x-circle"></i>
                {{ this.translate.btn_delete_content }}
              </div>
            </div>
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
          <button @click="this.hideModal(this.modalRemove);" type="button" class="btn-close" data-bs-dismiss="modal"
              aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {{ this.translate.modale_remove_body }} <br/>
          <span class="text-info"><i> {{ this.translate.modale_remove_body_2 }}</i></span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="this.hideModal(this.modalRemove);" data-bs-dismiss="modal">
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

      <div class="modal-content" :class="this.loading === true ? 'block-grid' : ''">

        <div v-if="this.loading" class="overlay">
          <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="txt-overlay">{{ this.translate.loading }}</span>
          </div>
        </div>
        
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi bi-plus-circle"></i> {{ this.translate.modale_new_title }}
          </h1>
          <button @click="this.hideModal(this.modalNew);this.resetDataNewContent()" type="button" class="btn-close" data-bs-dismiss="modal"
              aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label :for="'list-choice-content-' + this.renderBlockId" class="form-label">{{ this.translate.modale_new_choice_label }}</label>
            <select :id="'list-choice-content-' + this.renderBlockId" class="form-select" v-model="this.idSelectContent" @change="this.loadListContentType()">
              <option value="0">---</option>
              <option v-for="(value, key) in this.listeContent" :value="parseInt(key)">{{ value }}</option>
            </select>
            <div id="list-status-help" class="form-text">{{ this.translate.modale_new_choice_info }}</div>
          </div>

          <div v-if="this.typeContent.list.length !== 0" class="mb-3">
            <label :for="'list-choice-type-content-' + this.renderBlockId" class="form-label">{{ this.typeContent.label }}</label>
            <select :id="'list-choice-type-content-' + this.renderBlockId" class="form-select" v-model="this.idSelectTypeContent">
              <option v-for="(value, key) in this.typeContent.list" :value="parseInt(key)">{{ value }}</option>
            </select>
            <div id="list-status-help" class="form-text">{{ this.typeContent.help }}</div>
          </div>


        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="this.hideModal(this.modalNew);this.resetDataNewContent()" data-bs-dismiss="modal">
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
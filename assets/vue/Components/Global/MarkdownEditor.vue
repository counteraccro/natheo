<script>

/**
 * @author Gourdon Aymeric
 * @version 1.5
 * Editeur Markdown
 */

import {marked} from 'marked'
import {debounce} from 'lodash-es'
import {Modal} from 'bootstrap'
import MediaModalMarkdown from "../Mediatheque/MediaModalMarkdown.vue";
import axios from "axios";
import ModalNat from "./Modal.vue";

export default {
  name: "MarkdownEditor",
  components: {
    ModalNat,
    MediaModalMarkdown
  },
  props: {
    meId: String,
    meValue: String,
    meRows: Number,
    meTranslate: Object,
    meKeyWords: Object,
    meSave: Boolean,
    mePreview: Boolean,
  },
  emits: ['editor-value', 'editor-value-change'],
  data() {
    return {
      loading: false,
      value: this.meValue,
      valueRef: this.meValue,
      id: this.meId,
      modaleMedia: "",
      titleModal: "",
      linkModal: "",
      textModal: "",
      linkLabelModal: "",
      isImage: false,
      isValide: "",
      dataMedia: [],
      tabModal: {
        modalMarkdownEditor: false,
        modalInternalLink: false
      },
      urls: {
        urlMedia: "",
        urlPreview: "",
        urlSetPreview: "",
        urlLoadData: "/admin/fr/markdown/ajax/load-datas",
      },
      currentFolder: [],
      loadingMedia: false,
      cookieNamePreview: 'natheo-preview'
    }
  },
  mounted() {
    this.modaleMedia = new Modal(document.getElementById(this.getNameModale("modal-markdown-mediatheque")), {});
    this.loadData();
  },
  computed: {
    output() {
      return marked(this.value)
    }
  },
  methods: {
    update: debounce(function (e) {
      this.value = e.target.value
    }, 50),

    /** Charge les données nécessaires au fonctionnement de l'éditer **/
    loadData() {
      axios.get(this.urls.urlLoadData).then((response) => {
        this.urls.urlMedia = response.data.media;
        this.urls.urlPreview = response.data.preview;
        this.urls.urlSetPreview = response.data.initPreview;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
      });
    },

    randomIntFromInterval(min, max) {
      return Math.floor(Math.random() * (max - min + 1) + min)
    },

    /**
     * Retourne le nom de la modale fusionné avec un identifiant
     * @param name
     * @returns {string}
     */
    getNameModale(name) {
      return name + '-' + this.id;
    },

    /**
     * Ajoute la balise table
     */
    addTable() {
      let balise = '| Column 1 | Column 2 | Column 3 |\n' +
          '| -------- | -------- | -------- |\n' +
          '| Text     | Text     | Text     |';
      this.addElement(balise, 0, false);
    },

    /**
     * Ajoute la balise code
     */
    addCode() {
      let balise = '```\n' +
          '\n' +
          '```';
      this.addElement(balise, 4, false);
    },

    /**
     * Ajoute un lien ou une image
     * @param modal
     * @param image
     */
    addLink(modal, image) {
      if (modal) {
        this.linkModal = 'https://';
        if (image) {
          this.titleModal = this.meTranslate.modalTitreImage;
          this.linkLabelModal = this.meTranslate.modalInputUrlImage;
        } else {
          this.titleModal = this.meTranslate.modalTitreLink;
          this.linkLabelModal = this.meTranslate.modalInputUrlLink;
        }

        this.isImage = image;
        this.updateModale('modalMarkdownEditor', true);
      } else {
        let balise = '';
        if (image) {
          balise = '![' + this.textModal + '](' + this.linkModal + ')';
        } else {
          balise = '[' + this.textModal + '](' + this.linkModal + ')';
        }
        this.addElement(balise, 0, false);
        this.textModal = this.linkModal = '';
        this.closeModal('modalMarkdownEditor');
      }

      return false;
    },

    /*closeModal() {
      this.modal.hide();
    },*/

    /**
     * Ouvre la modale de la médiathèque
     */
    openModalMediatheque() {
      this.loadMedia(0, 'asc', 'created_at')
      this.modaleMedia.show();
    },

    loadMedia(folderId, order, filter) {

      this.loadingMedia = true;
      axios.get(this.urls.urlMedia + '/' + folderId + '/' + order + '/' + filter, {}).then((response) => {
        this.dataMedia = response.data.medias;
        this.currentFolder = response.data.currentFolder;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loadingMedia = false
      });
    },

    /**
     * Ferme la modale de la médiathèque
     */
    closeModalMediatheque() {
      this.modaleMedia.hide();
      this.dataMedia = [];
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModale(nameModale, state) {
      this.tabModal[nameModale] = state;
    },

    /**
     * Ferme une modale
     * @param nameModale
     */
    closeModal(nameModale) {
      this.updateModale(nameModale, false);
    },

    /**
     * Ajoute le média sélectionné
     * @param name
     * @param url
     * @param size
     */
    selectMedia(name, url, size) {

      let balise = '';
      let patt1 = /\.[0-9a-z]+$/i;
      let extensionImg = ['.jpeg', '.jpg', '.gif', '.tif', '.psd', '.svg', '.png'];

      if (extensionImg.includes(name.match(patt1)[0])) {

        let sizeHtml = '';
        switch (size) {
          case "fluid":
            sizeHtml = 'class="img-fluid"'
            break;
          case "max":
            sizeHtml = '';
            break;
          default:
            sizeHtml = 'width="' + size + 'px" height="' + size + 'px"';
        }

        balise = '<img src="' + url + '" alt="' + name + '" ' + sizeHtml + '>';
      } else {
        balise = '<a href="' + url + '" target="_blank">' + name + '</a>';
      }

      this.addElement(balise, 0, false);
      this.closeModalMediatheque();
    },

    /**
     * Ajoute un élément dans l'input
     * @param balise
     * @param position
     * @param separate
     * @returns {boolean}
     */
    addElement(balise, position, separate) {

      let input = document.getElementById("editor-" + this.id);
      let start = input.selectionStart;
      let end = input.selectionEnd;
      let value = this.value;

      let select = window.getSelection().toString();

      if (select === '') {
        this.value = value.slice(0, start) + balise + value.slice(end);
        input.value = this.value;
        let caretPos = start + balise.length;
        input.focus();
        input.setSelectionRange(caretPos - position, caretPos - position);

      } else {

        let before = value.slice(0, start);
        let after = value.slice(end);
        let replace = '';

        if (separate) {
          let b = balise.slice(balise.length / 2);
          replace = b + select.toString() + b;
        } else {
          replace = balise + select.toString()
        }

        this.value = before + replace + after;
        input.value = this.value;

        let caretPos = start + replace.length;
        input.focus();
        input.setSelectionRange(caretPos, caretPos);

      }
      return false;
    },

    isValideInput(event) {
      this.isValide = '';
      let value = event.target.value;
      if (value === "") {
        this.isValide = 'is-invalid';
      }
      this.$emit('editor-value-change', this.meId, value);
    },

    /**
     * Affiche la préview
     */
    openPreview() {

      this.loading = true;
      axios.post(this.urls.urlSetPreview, {
        value: this.value
      }).then((response) => {

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        window.open(this.urls.urlPreview, '_blank');
        this.loading = false
      });
    },

    /**
     * Ouvre la modale pour les liens internes et charge les données
     */
    openModalExternalLink()
    {
      this.loading = true;
      this.updateModale('modalInternalLink', true)
      this.loading = false;
    },

    /**
     * Check si on est dans le cas de données éditées non sauvegardées ou non
     * @returns {boolean}
     */
    checkNoSaveData() {
      return (this.meSave && (this.valueRef !== this.value));
    }
    ,

    /**
     * Condition pour les class du textarea
     * @returns {string}
     */
    classInputTextArea() {
      if (this.isValide !== '') {
        return this.isValide;
      }

      if (this.checkNoSaveData()) {
        return "border-3 border-warning"
      }
    }
    ,

    /**
     * Event sur le bouton save
     */
    eventBtnSave() {
      this.valueRef = this.value;
      this.$emit('editor-value', this.meId, this.value)
    }
  }
}
</script>

<template>

  <div id="block-faq" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.meTranslate.loading }}</span>
      </div>
    </div>

    <ModalNat
        :id="'modalMarkdownEditor'"
        :show="this.tabModal.modalMarkdownEditor"
        @close-modal="this.closeModal"
        :optionModalSize="'modal-lg'"
        :option-show-close-btn="false">
      <template #title>
        <i class="bi bi-plus-circle-fill"></i> {{ this.titleModal }}
      </template>
      <template #body>
        <div>
          <div class="mb-3">
            <label :for="'link-modal-' + this.id" class="form-label">{{ this.linkLabelModal }}</label>
            <input type="text" class="form-control" :id="'link-modal-' + this.id" placeholder="" v-model="linkModal">
          </div>
          <div class="mb-3">
            <label :for="'text-modal-' + this.id" class="form-label">Texte</label>
            <input type="text" class="form-control" :id="'text-modal-' + this.id" placeholder="" v-model="textModal">
          </div>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-primary" @click="addLink(false, this.isImage)"><i
            class="bi bi-check2-circle"></i> {{ this.meTranslate.modalBtnValide }}
        </button>
      </template>
    </ModalNat>

    <ModalNat
        :id="'modalInternalLink'"
        :show="this.tabModal.modalInternalLink"
        @close-modal="this.closeModal"
        :optionModalSize="'modal-lg'"
        :option-show-close-btn="false">
      <template #title>
        <i class="bi bi-link-45deg"></i> {{ this.meTranslate.modaleExternalLink.title }}
      </template>
      <template #body>
        body
      </template>
      <template #footer>
        <button type="button" class="btn btn-primary"><i
            class="bi bi-check2-circle"></i> {{ this.meTranslate.modalBtnValide }}
        </button>
      </template>
    </ModalNat>

    <div class="modal fade" :id="this.getNameModale('modal-markdown-mediatheque')" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <MediaModalMarkdown
              :medias="this.dataMedia"
              :translate="this.meTranslate.mediathequeMarkdown"
              :current-folder="this.currentFolder"
              :loading="this.loadingMedia"
              @close-modale="this.closeModalMediatheque"
              @select-media="this.selectMedia"
              @load-media="this.loadMedia"
          >
          </MediaModalMarkdown>
        </div>
      </div>
    </div>

    <div class="editor">
      <div class="header mb-2">
        <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('****', '2', true)"
            :title="this.meTranslate.btnBold">
          <i class="bi bi-type-bold"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('**', '1', true)"
            :title="this.meTranslate.btnItalic">
          <i class="bi bi-type-italic"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('~~~~', '2', true)"
            :title="this.meTranslate.btnStrike">
          <i class="bi bi-type-strikethrough"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('> ', '0', false)"
            :title="this.meTranslate.btnQuote">
          <i class="bi bi-quote"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('- ', '0', false)"
            :title="this.meTranslate.btnList">
          <i class="bi bi-list-ul"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('1. ', '0', false)"
            :title="this.meTranslate.btnListNumber">
          <i class="bi bi-list-ol"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addTable" :title="this.meTranslate.btnTable">
          <i class="bi bi-table"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addLink(true, false)" :title="this.meTranslate.btnLink">
          <i class="bi bi-link"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.openModalExternalLink" :title="this.meTranslate.btnLinkInterne">
          <i class="bi bi-link-45deg"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addLink(true, true)" :title="this.meTranslate.btnImage">
          <i class="bi bi-image"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.addCode" :title="this.meTranslate.btnCode">
          <i class="bi bi-code"></i></div>
        <div class="btn btn-secondary btn-sm me-1" @click="this.openModalMediatheque"
            :title="this.meTranslate.btnMediatheque">
          <i class="bi bi-images"></i></div>

        <div class="dropdown float-start me-1">
          <button class="btn btn-secondary btn-sm dropdown-toggle" :title="this.meTranslate.titreLabel" type="button"
              data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-type-h1"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item no-control" style="cursor: pointer" @click="this.addElement('# ', '0', false)">
              {{ this.meTranslate.titreH1 }}</a></li>
            <li><a class="dropdown-item no-control" style="cursor: pointer" @click="this.addElement('## ', '0', false)">
              {{ this.meTranslate.titreH2 }}</a></li>
            <li>
              <a class="dropdown-item no-control" style="cursor: pointer" @click="this.addElement('### ', '0', false)">
                {{ this.meTranslate.titreH3 }}</a></li>
            <li>
              <a class="dropdown-item no-control" style="cursor: pointer" @click="this.addElement('#### ', '0', false)">
                {{ this.meTranslate.titreH4 }}</a></li>
            <li>
              <a class="dropdown-item no-control" style="cursor: pointer" @click="this.addElement('##### ', '0', false)">
                {{ this.meTranslate.titreH5 }}</a></li>
            <li>
              <a class="dropdown-item no-control" style="cursor: pointer" @click="this.addElement('###### ', '0', false)">
                {{ this.meTranslate.titreH6 }}</a></li>
          </ul>
        </div>
        <div class="dropdown float-start me-1" v-if="this.meKeyWords.length !== 0">
          <button class="btn btn-secondary btn-sm dropdown-toggle" :title="this.meTranslate.btnKeyWord" type="button"
              data-bs-toggle="dropdown" aria-expanded="false">
            {{ this.meTranslate.btnKeyWord }} <i class="bi bi-key-fill"></i>
          </button>
          <ul class="dropdown-menu">
            <li v-for="(label, key) in this.meKeyWords">
              <a class="dropdown-item" style="cursor: pointer" @click="this.addElement('[[' + key + ']]', '0', false)">
                {{ label }}</a>
            </li>
          </ul>
        </div>
        <div class="float-end">
          <div class="btn btn-secondary btn-sm me-1" :title="this.meTranslate.preview" @click="this.openPreview()">
            <i class="bi bi-box-arrow-in-up-right"></i>
          </div>
          <div v-if="this.meSave" class="btn btn-secondary btn-sm me-1" @click="this.eventBtnSave"
              :title="this.meTranslate.btnSave">
            <i class="bi bi-save"></i></div>
        </div>
      </div>

      <textarea :id="'editor-'+ this.id" class="form-control" :class="this.classInputTextArea()" :value="this.value"
          @input="update" :rows="this.meRows" @change="this.isValideInput" @keyup="this.isValideInput"></textarea>
      <div class="invalid-feedback">
        {{ this.meTranslate.msgEmptyContent }}
      </div>
      <div :id="'emailHelp-' + this.id" class="form-text">
        <div v-html="this.meTranslate.help"></div>
        <div v-if="this.checkNoSaveData()">
          <b><i><span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i>
          <span v-html="this.meTranslate.warning_edit"></span></span></i></b>
        </div>
      </div>

      <fieldset class="mt-3" v-if="this.mePreview">
        <legend>{{ this.meTranslate.render }}</legend>
        <div class="markdown-editor-output" v-html="output"></div>
      </fieldset>
    </div>
  </div>
</template>

<style scoped>

</style>
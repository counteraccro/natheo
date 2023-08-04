<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage modal pour ajouter/editer un dossier
 */

import {Modal} from 'bootstrap'
import axios from "axios";
import {isEmpty} from "lodash-es";

export default {
  props: {
    openModal: Boolean,
    currentFolderId: Number,
    folderEdit: Array,
    translate: Array
  },
  emits: ['hide-modal-folder'],
  data() {
    return {
      modal: "",
    }
  },
  created() {},
  mounted() {
    this.modal = new Modal(document.getElementById("modal-add-edit-folder"), {});
  },
  computed: {},
  methods: {
    isEmpty,

    /**
     * Permet d'afficher la modale
     */
    showModal() {

      this.modal.show();
    },

    /**
     * Permet de masquer la modale
     */
    hideModal() {
      this.modal.hide();
    },


    /**
     * Génère le titre de la modale
     */
    getTitleModal() {
      if (isEmpty(this.folderEdit)) {
        return this.translate.new;
      } else {
        return this.translate.edit + ' ' + this.folderEdit.name;
      }
    }
  }
}
</script>

<template>

    <span v-if="this.openModal ? this.showModal():''"></span>
    <div class="modal fade" id="modal-add-edit-folder" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-secondary">
            <h1 class="modal-title fs-5 text-white">
              <i class="bi" :class="isEmpty(this.folderEdit)?'bi-folder-plus': 'bi-pencil-fill'"></i> {{ this.getTitleModal() }}
            </h1>
            <button type="button" class="btn-close" @click="this.hideModal();$emit('hide-modal-folder', false)"></button>
          </div>
          <div class="modal-body">
            Contenu
          </div>
          <div class="modal-footer">
            Footer
          </div>
        </div>
      </div>
    </div>
</template>

<style scoped>

</style>
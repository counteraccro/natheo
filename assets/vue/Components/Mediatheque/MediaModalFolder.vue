<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage modal pour ajouter/editer un dossier
 */

import {Modal} from 'bootstrap'

export default {
  props: {
    openModal: Boolean,
    translate: Array
  },
  emits: ['hide-modal-folder'],
  data() {
    return {
      modal : "",
    }
  },
  created() {
    //this.$root.$refs.MediaModalFolder = this;
  },
  mounted() {
    this.modal = new Modal(document.getElementById("modal-add-edit-folder"), {});
  },
  computed: {},
  methods: {
    showModal() {
      this.modal.show();
    },

    hideModal()
    {
      this.modal.hide();
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
          <h1 class="modal-title fs-5 text-white"><i class="bi bi-folder-plus"></i> {{ this.translate.new }}</h1>
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
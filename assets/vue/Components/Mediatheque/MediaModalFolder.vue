<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage modal pour ajouter/editer un dossier
 */

import axios from "axios";
import {isEmpty} from "lodash-es";

export default {
  props: {
    currentFolderId: Number,
    folderEdit: Array,
    translate: Array,
    name: String
  },
  emits: ['hide-modal-folder'],
  data() {
  },
  created() {
    console.log('created')
  },
  mounted() {
    console.log('mounted')
  },
  computed: {
  },
  methods: {
    isEmpty,

    /**
     * Créer ou modifie un dossier
     */
    submit() {

      let name = document.getElementById('folderName');

      console.log(name.value);
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

  <div class="modal-header bg-secondary">
    <h1 class="modal-title fs-5 text-white">
      <i class="bi" :class="isEmpty(this.folderEdit)?'bi-folder-plus': 'bi-pencil-fill'"></i> {{ this.getTitleModal() }}
    </h1>
    <button type="button" class="btn-close" @click="$emit('hide-modal-folder', false)"></button>
  </div>
  <div class="modal-body">
    <div class="mb-3">
      <label for="folderName" class="form-label">{{ this.translate.input_label }}</label>
      <input type="text" :value="this.name" class="form-control" id="folderName" :placeholder="this.translate.input_label_placeholder">
      <div class="invalid-feedback">
        {{ this.translate.input_error }}
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <div class="btn btn-dark" @click="$emit('hide-modal-folder', false)">{{ this.translate.btn_cancel }}</div>
    <div v-if="isEmpty(this.folderEdit)" class="btn btn-primary">{{ this.translate.btn_submit_create }}</div>
    <div v-else class="btn btn-primary" @click="this.submit()">{{ this.translate.btn_submit_edit }}</div>
  </div>

</template>

<style scoped>

</style>
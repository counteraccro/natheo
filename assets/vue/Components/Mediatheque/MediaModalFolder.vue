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
    return {
      name: this.name,
    }
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
      console.log('oki submit')
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
      <label for="foldeName" class="form-label">Nom du dossier -trad</label>
      <input type="text" v-model="this.name" class="form-control" id="foldeName" placeholder="Nom du dossier -trad">
    </div>
  </div>
  <div class="modal-footer">
    <div class="btn btn-dark" @click="$emit('hide-modal-folder', false)">Annuler trad</div>
    <div v-if="isEmpty(this.folderEdit)" class="btn btn-primary">Créer le dossier trad</div>
    <div v-else class="btn btn-primary" @click="this.submit()">Modifier le dossier trad</div>
  </div>

</template>

<style scoped>

</style>
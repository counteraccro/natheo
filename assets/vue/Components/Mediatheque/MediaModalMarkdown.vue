<script>

import MediasBreadcrumb from "./MediasBreadcrumb.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage du contenu de la modale de choix de média depuis l'editeur Markdown
 */
export default {
  name: 'MediaModalMarkdown',
  components: {MediasBreadcrumb},
  props: {
    medias: Object,
    translate: Object,
    currentFolder: Object,
    loading: Boolean
  },
  emits: ['close-modale', 'select-media', 'load-media'],
  data() {
    return {
      size: 'fluid'
    }
  },
  computed: {},
  methods: {

    /**
     * Charge les médias depuis un dossier
     * @param id
     */
    loadMediaInFolder(id) {
      this.$emit('load-media', id, 'asc', 'created_at')
    },

    /**
     * Retourne la taille du dossier
     * @returns {*|string}
     */
    getSizeCurrentFolder() {
      return this.currentFolder.size;
    },

    /**
     * Setter pour la taille de l'image
     * @param size
     */
    setSize(size) {
      this.size = size
    },

    /**
     * Retourne le label du bouton de choix
     */
    getLabelBtnSize() {
      let str = '';
      switch (this.size) {
        case "fluid":
          str = '<i class="bi bi-aspect-ratio"></i> ' + this.translate.size_fluide;
          break;
        case "max":
          str = '<i class="bi bi-card-image"></i> ' + this.translate.size_max;
          break;
        case "100":
          str = '<i class="bi bi-textarea-resize"></i> ' + this.translate.size_100;
          break;
        case "200":
          str = '<i class="bi bi-textarea-resize"></i> ' + this.translate.size_200;
          break;
        case "300":
          str = '<i class="bi bi-textarea-resize"></i> ' + this.translate.size_300;
          break;
        case "400":
          str = '<i class="bi bi-textarea-resize"></i> ' + this.translate.size_400;
          break;
        case "500":
          str = '<i class="bi bi-textarea-resize"></i> ' + this.translate.size_500;
          break;
        default:
          str = '<i class="bi bi-aspect-ratio"></i> ' + this.translate.size_fluide;
      }
      return this.translate.btn_size + str;
    }
  }
}
</script>

<template>
  <div class="modal-header bg-secondary">
    <h1 class="modal-title fs-5 text-white">
      <i class="bi bi-info-circle-fill"></i> {{ this.translate.title }}
    </h1>
    <button type="button" class="btn-close" @click="$emit('close-modale')"></button>
  </div>
  <div class="modal-body">

    <div class="card border border-secondary" :class="this.loading === true ? 'block-grid' : ''">
      <div v-if="this.loading" class="overlay">
        <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="txt-overlay">{{ this.translate.loading }}</span>
        </div>
      </div>
      <div class="card-header text-bg-secondary">
        <div class="float-end">
          {{ this.getSizeCurrentFolder() }}
        </div>
        <MediasBreadcrumb
            :paths="this.currentFolder.root"
            @load-folder="this.loadMediaInFolder"
        >
        </MediasBreadcrumb>
      </div>
      <div class="card-body">
        <div class="dropdown float-end">
          <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span v-html="this.getLabelBtnSize()"></span>
          </button>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('fluid')"><i class="bi bi-aspect-ratio"></i> {{ this.translate.size_fluide }}</a>
            </li>
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('max')"><i class="bi bi-card-image"></i> {{ this.translate.size_max }}</a>
            </li>
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('100')"><i class="bi bi-textarea-resize"></i> {{ this.translate.size_100 }}</a>
            </li>
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('200')"><i class="bi bi-textarea-resize"></i> {{ this.translate.size_200 }}</a>
            </li>
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('300')"><i class="bi bi-textarea-resize"></i> {{ this.translate.size_300 }}</a>
            </li>
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('400')"><i class="bi bi-textarea-resize"></i> {{ this.translate.size_400 }}</a>
            </li>
            <li>
              <a class="dropdown-item" style="cursor: pointer" @click="this.setSize('500')"><i class="bi bi-textarea-resize"></i> {{ this.translate.size_500 }}</a>
            </li>
          </ul>
        </div>
        <div id="block-media-grid-markdown" class="mt-5 row">
          <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
            <img v-if="media.type === 'media'" height="100" width="100" class="rounded-3"
                :src="media.thumbnail" style="cursor:pointer;"
                :alt="media.name" @click="$emit('select-media', media.name, media.webPath, this.size)"/>
            <div v-else class="folder" alt="media.name" @click="$emit('load-media', media.id, 'asc', 'created_at')"></div>
            <div class="info-media text-center">
            <span class="d-inline-block text-truncate" style="max-width: 100px;vertical-align: middle;">
              {{ media.name }}
            </span>
            </div>
          </div>
          <div v-else class="text-center">
            <i>{{ this.translate.no_media }}</i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <div class="btn btn-dark" @click="$emit('close-modale')">{{ this.translate.btn_close }}</div>
  </div>
</template>

<style scoped>

</style>
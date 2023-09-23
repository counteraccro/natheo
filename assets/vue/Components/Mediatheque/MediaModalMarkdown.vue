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
    return {}
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
          {{ this.getSizeCurrentFolder()   }}
        </div>
        <MediasBreadcrumb
            :paths="this.currentFolder.root"
            @load-folder="this.loadMediaInFolder"
        >
        </MediasBreadcrumb>
      </div>
      <div class="card-body">

        <div id="block-media-grid-markdown" class="mt-5 row">
          <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
            <img v-if="media.type === 'media'" height="100" width="100" class="rounded-3"
                :src="media.thumbnail" style="cursor:pointer;"
                :alt="media.name" @click="$emit('select-media', media.name, media.webPath)"/>
            <div v-else class="folder" alt="media.name" @click="$emit('load-media', media.id, 'asc', 'created_at')"></div>
            <div class="info-media rounded-bottom-3">
            <span class="d-inline-block text-truncate" style="max-width: 140px;vertical-align: middle;"> {{
                media.name
                                                                                                         }} </span>
            </div>
          </div>
        </div>
        <div class="btn btn-info" @click="$emit('select-media', 'mediaMan', '/aa/bb/aa.jpg')">Test</div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <div class="btn btn-dark" @click="$emit('close-modale')">{{ this.translate.btn_close }}</div>
  </div>
</template>

<style scoped>

</style>
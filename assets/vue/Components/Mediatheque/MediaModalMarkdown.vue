<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage du contenu de la modale de choix de média depuis l'editeur Markdown
 */
export default {
  name: 'MediaModalMarkdown',
  props: {
    medias: Object,
    translate: Object
  },
  emits: ['close-modale', 'select-media', 'load-media'],
  data() {
    return {}
  },
  computed: {},
  methods: {}
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
    <div id="block-media-grid" class="mt-5 row">
    <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
      <img v-if="media.type === 'media'" height="100" width="100" class="rounded-3"
           :src="media.thumbnail" style="cursor:pointer;"
           :alt="media.name"  @click="$emit('select-media', media.name, media.webPath)" />
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
  <div class="modal-footer">
    <div class="btn btn-dark" @click="$emit('close-modale')">{{ this.translate.btn_close }}</div>
  </div>
</template>

<style scoped>

</style>
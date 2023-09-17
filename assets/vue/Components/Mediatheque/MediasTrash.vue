<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID des médias dans la corbeille
 */
export default {
  props: {
    medias: Object,
    translate: Object,
  },
  emits: ['revert-trash', 'delete'],
  data() {
    return {}
  },
  computed: {},
  methods: {

    /**
     * Permet d'ouvrir un média
     * @param path
     */
    openMedia(path) {
      window.open(path, "_blank");
    }
  }
}
</script>

<template>

  <div id="block-media-grid" class="mt-3 row">
    <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
      <img v-if="media.type === 'media'" height="200" width="200"
          :src="media.thumbnail" style="cursor:pointer;"
          :alt="media.name" @click="this.openMedia(media.webPath)"/>
      <div v-else class="folder" alt="media.name"></div>
      <div class="info-media">
        <div class="btn-group">
          <button type="button" class="btn btn-link btn-sm dropdown-toggle pt-2" style="color: #FFFFFF; margin-top: -5px;" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-justify"></i>
          </button>
          <ul class="dropdown-menu">
            <li>
              <!-- lien annuler -->
              <a class="dropdown-item" @click="$emit('revert-trash', media.type, media.id)">
                <i class="bi bi-arrow-counterclockwise"></i> {{ this.translate.link_revert }}
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" @click="$emit('delete', media.type, media.id, media.name, false)">
              <i class="bi bi-trash-fill"></i> {{ this.translate.link_delete }}
            </a>
            </li>
          </ul>
        </div>
        <span class="d-inline-block text-truncate" style="max-width: 140px;vertical-align: middle;"> {{ media.name }} </span>

      </div>
    </div>
    <div v-else class="text-center mt-3">
      <i class="bi bi-folder-x"></i> <i>
      {{ this.translate.no_media }}
    </i>
    </div>
  </div>


</template>

<style scoped>

</style>
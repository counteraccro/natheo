<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID des médias
 */
export default {
  props: {
    medias: Object,
    translate: Object,
    render: String
  },
  emits: ['load-data-folder', 'edit-folder', 'show-info', 'edit-media', 'move', 'trash'],
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

  <div id="block-media-grid" class="mt-3 row" v-if="this.render === 'grid'">
    <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
      <img v-if="media.type === 'media'" height="200" width="200"
          :src="media.thumbnail" style="cursor:pointer;"
          :alt="media.name" @click="this.openMedia(media.webPath)"/>
      <div v-else class="folder" alt="media.name" @click="$emit('load-data-folder', media.id)"></div>
      <div class="info-media">
        <div class="btn-group">
          <button type="button" class="btn btn-link btn-sm dropdown-toggle pt-2" style="color: #FFFFFF; margin-top: -5px;" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-justify"></i>
          </button>
          <ul class="dropdown-menu">
            <li>
              <!-- lien information -->
              <a class="dropdown-item" @click="$emit('show-info', media.type, media.id)">
                <i class="bi bi-info-circle-fill"></i> {{ this.translate.link_info }}
              </a>
            </li>
            <li>
              <!-- lien éditer -->
              <a v-if="media.type === 'media'" class="dropdown-item" @click="$emit('edit-media', media.id)">
                <i class="bi bi-pencil-square"></i> {{ this.translate.link_edit_media }}
              </a>
              <a v-else class="dropdown-item" @click="$emit('edit-folder', media.id)">
                <i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}
              </a>
            </li>
            <li>
              <a class="dropdown-item"  @click="$emit('move', media.type, media.id)">
                <i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.link_move }}
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" @click="$emit('trash', media.type, media.id, media.name, false)">
              <i class="bi bi-trash-fill"></i> {{ this.translate.link_remove }}
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
  <div v-else>
    Render list
  </div>


</template>

<style scoped>

</style>
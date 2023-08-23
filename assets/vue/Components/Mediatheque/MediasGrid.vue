<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID des médias
 */
export default {
  props: {
    medias: Object,
    translate: Object
  },
  emits: ['load-data-folder', 'edit-folder', 'show-info'],
  data() {
    return {}
  },
  computed: {},
  methods: {}
}
</script>

<template>

  <div id="block-media-grid" class="mt-3 row">
    <div class="media col-auto mb-4" v-for="media in this.medias">
      <img v-if="media.type === 'media'" height="200" width="200" :src="media.thumbnail" :alt="media.name"/>
      <div v-else class="folder" alt="media.name" @click="$emit('load-data-folder', media.id)"></div>
      <div class="info-media">
        <div class="btn-group">
          <button type="button" class="btn btn-link btn-sm dropdown-toggle" style="color: #FFFFFF; margin-top: -5px;" data-bs-toggle="dropdown" aria-expanded="false">
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
              <a v-if="media.type === 'media'" class="dropdown-item">
                <i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}
              </a>
              <a v-else class="dropdown-item" @click="$emit('edit-folder', media.id)">
                <i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}
              </a>
            </li>
            <li>
              <a class="dropdown-item"><i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.link_move }}</a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item"><i class="bi bi-trash-fill"></i> {{ this.translate.link_remove }}</a>
            </li>
          </ul>
        </div>
       <span class="d-inline-block text-truncate" style="max-width: 140px;"> {{ media.name }} </span>

      </div>
    </div>
  </div>


</template>

<style scoped>

</style>
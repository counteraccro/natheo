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
  emits: ['load-data-folder', 'edit-folder'],
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
      <img v-else src="/assets/natheo/mediatheque/folder-media.png" width="200" height="200" alt="media.name" @click="$emit('load-data-folder', media.id)">
      <div class="info-media">
        <div class="btn-group">
          <button type="button" class="btn btn-link btn-sm dropdown-toggle" style="color: #FFFFFF; margin-top: -5px;" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-justify"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">
              <i class="bi bi-info-circle-fill"></i> {{ this.translate.link_info }}</a>
            </li>
            <li v-if="media.type === 'media'">
              <a class="dropdown-item" href="#"><i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}</a>
            </li>
            <li v-else>
              <a class="dropdown-item" href="#" @click="$emit('edit-folder', media.id)">
                <i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="#"><i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.link_move }}</a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-trash-fill"></i> {{ this.translate.link_remove }}</a>
            </li>
          </ul>
        </div>
        {{ media.name }}

      </div>
    </div>
  </div>


</template>

<style scoped>

</style>
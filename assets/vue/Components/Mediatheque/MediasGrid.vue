<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID des médias
 */
export default {
  props: {
    medias: Array,
  },
  emits: ['load-folder'],
  data() {
    return {

    }
  },
  computed: {},
  methods: {}
}
</script>

<template>

  <div id="block-media-grid" class="mt-3">
    <div class="media float-start me-4" v-for="media in this.medias">
      <img height="200" width="200" :src="media.thumbnail" :alt="media.name" v-if="media.type === 'media'"/>
      <img v-else src="/assets/natheo/mediatheque/folder-media.png" width="200" height="200" alt="media.name" @click="$emit('load-folder', media.id)">
      <div class="info-media">
        {{ media.name }}
      </div>
    </div>
  </div>


</template>

<style scoped>

</style>
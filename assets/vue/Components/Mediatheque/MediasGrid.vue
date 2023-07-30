<script>
/**
 * Affichage des médias au format GRID
 */
export default {
  props: {
    medias: Array,
  },
  //emits: ['redirect-action'],
  data() {
    return {
      sortKey: '',
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
      <img src="/assets/natheo/mediatheque/folder-media.png" width="200" height="200" alt="media.name" v-else>
      <div class="info-media">
        {{ media.name }}
      </div>
    </div>
  </div>


</template>

<style scoped>

</style>
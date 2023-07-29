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
  computed: {

  },
  methods: {

  }
}
</script>

<template>
  <div class="mt-5" v-for="media in this.medias">

    {{ media.name }}
    <img class="img-thumbnail" :src="media.thumbnail" :alt="media.name" v-if="media.type === 'media'"/>

    <img src="/assets/natheo/mediatheque/folder-media.png" alt="aa">
  </div>
</template>

<style scoped>

</style>
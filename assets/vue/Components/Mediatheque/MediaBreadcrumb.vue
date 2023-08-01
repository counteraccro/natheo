<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage de la navBar des médias
 */
export default {
  props: {
    paths: Array,
  },
  emits: ['load-folder'],
  data() {
    return {}
  },
  computed: {},
  methods: {}
}
</script>

<style>

</style>

<template>

  <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" id="mediaBreadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="#" class="text-white">Home</a></li>
      <li class="breadcrumb-item active text-white" aria-current="page">Library</li>
    </ol>
  </nav>
</template>
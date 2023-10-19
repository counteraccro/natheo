<script>
/**
 * Permet de récupérer un tag pour le lier à autre chose
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'Tag',
  props: {
    urls: Object,
  },
  emits: ['add-tag', 'remove-tag'],
  data() {
    return {
      translate : [],
      resultSearch : []
    }
  },
  mounted() {

  },
  computed: {},
  methods: {

    loadData()
    {
      
    }

  }
}
</script>

<template>
 <div id="tag-component">
  <h5>{{ this.translate.title }}</h5>
 </div>

</template>

<style scoped>

</style>
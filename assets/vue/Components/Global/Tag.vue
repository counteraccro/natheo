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
    ListTags: Object,
  },
  emits: ['add-tag', 'remove-tag'],
  data() {
    return {
      translate : [],
    }
  },
  mounted() {

  },
  computed: {},
  methods: {}
}
</script>

<template>

</template>

<style scoped>

</style>
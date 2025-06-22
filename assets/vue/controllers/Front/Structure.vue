<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Structure',
  props: {
    slug: String,
    urls: Array,
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },

  methods: {

  }
}
</script>

<template>
  <div class="markdown-editor-output" v-html="this.slug"></div>
</template>
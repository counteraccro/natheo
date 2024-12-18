<script>
/**
 * Affiche un texte au format markdown
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {marked} from "marked";


export default {
  name: 'PreviewContent',
  components: {},
  props: {
    pContent: Object,
  },
  emits: [],
  data() {
    return {
    }
  },
  methods: {
    getMarked(value) {
      return marked(value)
    }
  }
}
</script>

<template>
  <div  v-if="this.pContent.type === 1">
    <div v-html="this.getMarked(this.pContent.content)"></div>
  </div>
  <div v-if="this.pContent.type === 2">
    FAQ
  </div>
  <div v-if="this.pContent.type === 3">
    Listing
  </div>
</template>
<script>
import {marked} from 'marked'

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu de type texte
 */
export default {
  name: 'ContentText',
  props: {
    data: String,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },
  computed: {
    output() {
      return marked(this.data);
    },
  },
  methods: {

  }
}
</script>

<template>
  <div class="natheo-content-text" v-html="this.output"></div>
</template>
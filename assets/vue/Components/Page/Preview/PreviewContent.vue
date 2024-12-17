<script>
/**
 * Affiche un texte au format markdown
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {marked} from "marked";


export default {
  name: 'PreviewContent',
  props: {
    pValue: String,
  },
  emits: [],
  data() {
    return {
      value: '',
    }
  },
  mounted() {
    this.value = marked(this.pValue);
  },
}
</script>

<template>
  aaa
  <div v-html="this.value"></div>
</template>
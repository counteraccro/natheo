<script>
/**
 * Permet d'afficher une modale de confirmation si un champ à été modifié sans sauvegarde
 * @author Gourdon Aymeric
 * @version 1.0
 */
import { marked } from 'marked';

export default {
  name: 'Preview',
  props: {
    pValue: String,
  },
  emits: [],
  data() {
    return {
      value: '',
    };
  },
  mounted() {
    this.value = marked(this.pValue);
  },
};
</script>

<template>
  <div class="markdown-editor-output" v-html="this.value"></div>
</template>

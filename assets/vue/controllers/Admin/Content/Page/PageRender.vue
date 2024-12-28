<script>
/**
 * Permet de générer un rendu pour les pages
 * TODO Abandonné pour le moment
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {emitter} from "../../../../../utils/useEvent";


export default {
  name: 'PageRender',
  props: {
    url: String,
    translate: Object,
  },
  emits: [],
  data() {
    return {
      page: [],
    }
  },
  mounted() {
    // Appelé depuis autoSave de Page.vue
    emitter.on('load-render', async () => {
     this.load();
    });
  },
  computed: {},
  methods: {
    load() {
      console.log('reload-render')
    }
  }
}
</script>

<template>
  <div>render</div>

</template>
<script>
/**
 * Permet de générer un rendu pour les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {emitter} from "../../../../../utils/useEvent";


export default {
  name: 'PagePreview',
  props: {
    urls: Object,
    datas: Object,
    translate: Object,
  },
  emits: [],
  data() {
    return {
      page: [],
    }
  },
  mounted() {
     this.load();
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
  <div>
    {{ this.datas.token }}
  </div>

</template>
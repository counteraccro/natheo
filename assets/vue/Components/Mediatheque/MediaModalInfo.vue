<script>
import {isEmpty} from "lodash-es";
import IsEmpty from "lodash-es/isEmpty";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage du contenu de la modale d'information
 */
export default {
  props: {
    data: Object,
    translate: Object
  },
  emits: [],
  data() {
    return {}
  },
  computed: {},
  methods: {
    IsEmpty
  }
}
</script>

<template>
  <div class="row">
    <div class="col-8">
      <div v-if="!IsEmpty(this.data)" v-for="(data, label) in this.data.data">
        {{ label }} : {{ data }}
      </div>
    </div>
    <div class="col-4 d-flex justify-content-center align-items-center text-center">
      <div v-if="!IsEmpty(this.data) && this.data.type === 'folder'">
        <img src="/assets/natheo/mediatheque/folder-media.png" alt="folder" height="100"/>
      </div>
      <div v-else-if="!IsEmpty(this.data) && this.data.type === 'media'">
        <img :src="this.data.thumbnail" :alt="this.data.thumbnail" class="img-fluid"/> <br/>
        <a class="link-dark" :href="this.data.web_path" target="_blank"><i class="bi bi-arrows-angle-expand"></i> {{ this.translate.link_size }}</a>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>
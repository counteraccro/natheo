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
  emits: ['close-modale'],
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
  <div class="modal-header bg-secondary">
    <h1 class="modal-title fs-5 text-white">
      <i class="bi bi-info-circle-fill"></i> {{ this.translate.title }}
    </h1>
    <button type="button" class="btn-close" @click="$emit('close-modale')"></button>
  </div>
  <div class="modal-body">
    <div class="row">
      <div class="col-8">
        <div v-if="!IsEmpty(this.data)" v-for="(data, label) in this.data.data">
          {{ label }} : {{ data }}
        </div>
      </div>
      <div class="col-4 d-flex justify-content-center align-items-center text-center">
        <div v-if="!IsEmpty(this.data) && this.data.type === 'folder'">
          <img src="/assets/natheo/mediatheque/folder.png" alt="folder" height="100"/>
        </div>
        <div v-else-if="!IsEmpty(this.data) && this.data.type === 'media'">
          <img :src="this.data.thumbnail" :alt="this.data.thumbnail" class="img-fluid"/> <br/>
          <a class="link-dark" :href="this.data.web_path" target="_blank">
            <span v-if="this.data.media_type === 'img'"><i class="bi bi-arrows-angle-expand"></i> {{ this.translate.link_size }} </span>
            <span v-else><i class="bi bi-download"></i> {{ this.translate.link_download }} </span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <div class="btn btn-dark" @click="$emit('close-modale')">{{ this.translate.btn_close }}</div>
  </div>

</template>

<style scoped>

</style>
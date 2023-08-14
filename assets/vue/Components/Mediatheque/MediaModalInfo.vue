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
    <div class="col-4 d-flex justify-content-center align-items-center">
      <div v-if="!IsEmpty(this.data) && this.data.type === 'folder'">
        <img src="/assets/natheo/mediatheque/folder-media.png" alt="folder" width="75" height="75"/>
      </div>
      <div v-else-if="!IsEmpty(this.data) && this.data.type === 'media'">
        <img :src="this.data.thumbnail" alt="folder" width="75" height="75"/>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>
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
  <div v-if="!IsEmpty(this.data) && this.data.type === 'folder'">
    <div v-for="(data, label) in this.data.data">
      {{ label }} : {{ data }}
    </div>
  </div>
  <div v-else-if="!IsEmpty(this.data) && this.data.type === 'media'">
    Media
  </div>
</template>

<style scoped>

</style>
<script>/**
 * Interface de la médiathèque
 */
import axios from "axios";

export default {
  name: "Mediatheque",
  props: {
    url: String
  },
  data() {
    return {
      loading: false,
      medias: [],
    }
  },

  mounted() {
    this.loadMedia();
  },

  methods: {

    /**
     * Charge les médias
     */
    loadMedia() {
      this.loading = true;

      axios.post(this.url, {
        'folder': null
      }).then((response) => {
          this.medias = response.data.medias;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
      });
    }
  }
}

</script>


<template>
  <div v-for="media in this.medias">
    {{media.name}}
    <img :src="media.web_path" :alt="media.name" />
  </div>

</template>

<style scoped>

</style>
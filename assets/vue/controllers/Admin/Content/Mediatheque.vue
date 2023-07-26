<script>/**
 * Interface de la médiathèque
 */
import axios from "axios";

export default {
  name: "Mediatheque",
  props: {
    url: String,
    translate: []
  },
  data() {
    return {
      loading: false,
      medias: [],
      folders: [],
      currentFolder: [
      ],
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
        this.folders = response.data.folders;
        this.currentFolder = response.data.currentFolder;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
      });
    },

    /**
     * Retourne le path du dossier courant
     * @returns {string}
     */
    getPathCurrentFolder() {
      if (this.currentFolder.folder === null) {
        return '/';
      } else {
        return "A faire";
      }
    },

    /**
     * Retourne la taille du dossier
     * @returns {*|string}
     */
    getSizeCurrentFolder() {
      if (this.currentFolder.size === null) {
        return '/';
      } else {
        return this.currentFolder.size;
      }
    }
  }
}

</script>


<template>

  <div :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="float-end">
          {{ this.getSizeCurrentFolder() }}
        </div>
        {{ this.getPathCurrentFolder() }}
      </div>
      <div class="card-body">
        <h5 class="card-title">Special title treatment</h5>
        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>

    <div v-for="media in this.medias">
      {{ media.name }}
      <img :src="media.webPath" :alt="media.name"/>
    </div>
  </div>

</template>

<style scoped>

</style>
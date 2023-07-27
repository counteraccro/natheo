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
      currentFolder: [],
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

    <div class="card border border-secondary">
      <div class="card-header text-bg-secondary">
        <div class="float-end">
          {{ this.getSizeCurrentFolder() }}
        </div>
        {{ this.getPathCurrentFolder() }}
      </div>
      <div class="card-body">
        <div>
          <div class="btn btn-secondary me-1"><i class="bi bi-folder-plus"></i> {{ this.translate.btn_new_folder }}
          </div>
          <div class="btn btn-secondary me-1"><i class="bi bi-file-plus"></i> {{ this.translate.btn_new_media }}</div>
          <div class="float-end">
            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-secondary dropdown-toggle me-1" data-bs-toggle="dropdown" aria-expanded="false">
                {{ this.translate.btn_filtre }} <i class="bi bi-clock"></i>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="bi bi-clock"></i> {{ this.translate.filtre_date }}</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-card-text"></i> {{ this.translate.filtre_nom }}</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file"></i> {{ this.translate.filtre_type }}</a></li>
              </ul>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-secondary dropdown-toggle me-1" data-bs-toggle="dropdown" aria-expanded="false">
                {{ this.translate.btn_order }} <i class="bi bi-sort-down"></i>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="bi bi-sort-down"></i> {{ this.translate.order_asc }}</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-sort-up"></i> {{ this.translate.order_desc }}</a></li>
              </ul>
            </div>
            <div class="btn btn-sm btn-secondary me-1"><i class="bi bi-grid"></i></div>
            <div class="btn btn-sm btn-secondary"><i class="bi bi-list"></i></div>
          </div>
        </div>

        <div class="mt-5" v-for="media in this.medias">
          {{ media.name }}
          <img class="img-thumbnail" :src="media.webPath" :alt="media.name"/>
        </div>

      </div>
    </div>
  </div>

</template>

<style scoped>

</style>
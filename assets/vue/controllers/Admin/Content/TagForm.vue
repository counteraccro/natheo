<script>/**
 * Permet d'ajouter ou éditer un tag
 * @author Gourdon Aymeric
 * @version 1.0
 */

import axios from "axios";

export default {
  name: "TagForm",
  props: {
    url_form_tag: String,
    translate: [],
    tag: []
  },
  data() {
    return {
      loading: false,
    }
  },
  mounted() {
  },

  methods: {

    /**
     * Récupère les données liées à la gestion des emails
     */
    save() {

      this.loading = true;

      axios.post(this.url_form_tag, {}).then((response) => {

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
      });
    },

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

    <br />
    <div v-if="this.tag !== null">
      {{ tag.id }}
      <div v-for="translation in tag.tagTranslations">
        {{translation.label}}
      </div>
    </div>


  </div>

</template>

<style scoped>

</style>
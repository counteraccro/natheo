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
    locales: [],
    pTag: []
  },
  data() {
    return {
      loading: false,
      tag: this.pTag
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

    <br/>
    <div class="row">
      <div class="col">

        <div class="card border-secondary">
          <div class="card-header bg-secondary text-white">
            <span v-if="tag.id === null">
              {{ this.translate.formTitleCreate }}
            </span>
            <span v-else>
              {{ this.translate.formTitleUpdate }} #{{ this.tag.id }}
            </span>
          </div>
          <div class="card-body">

            <label for="tagColor" class="form-label">{{ this.translate.formInputColorLabel }}</label>
            <input type="color" class="form-control form-control-color" id="tagColor" v-model="this.tag.color">

            <div v-for="translation in tag.tagTranslations">
              <div class="mb-3">
                <label :for="'label-' + translation.locale" class="form-label">{{ this.translate.formInputLabelLabel }}</label>
                <input type="text" class="form-control" :id="'label-' + translation.locale" placeholder="" v-model="translation.label">
              </div>

            </div>
          </div>
        </div>


      </div>
      <div class="col">
        <div class="card border-secondary">
          <div class="card-header bg-secondary text-white">
            Featured
          </div>
          <div class="card-body">
            <h5 class="card-title">Special title treatment</h5>
            <div v-for="translation in tag.tagTranslations">
              <span class="badge rounded-pill badge-nat" :style="'background-color: ' + tag.color"> {{ translation.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>

</template>

<style scoped>

</style>
<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de gérer les traductions de l'application
 */

import axios from "axios";

export default {
  name: "Translate",
  props: {
    url_langue: String,
    url_translate_file: String
  },
  data() {
    return {
      trans: [],
      currentLanguage: '',
      files: [],
      languages: [],
    }
  },
  mounted() {
    this.loadListeLanguages()
  },
  methods: {
    /**
     * Charge la liste des langues
     */
    loadListeLanguages() {
      axios.post(this.url_langue, {}).then((response) => {
        this.languages = response.data.languages;
        this.trans = response.data.trans;
      }).catch((error) => {
        console.log(error);
      }).finally();
    },

    /**
     * Event de choix de la langue
     * @param event
     */
    selectLanguage(event) {
      this.currentLanguage = event.target.value;
      if (this.currentLanguage !== "") {
        this.loadTranslateFile();
      }
    },

    loadTranslateFile() {
      axios.post(this.url_translate_file, {
        'language': this.currentLanguage
      }).then((response) => {
        this.files = response.data.files;
      }).catch((error) => {
        console.log(error);
      }).finally();
    }
  }
}
</script>

<template>
  <div class="row">
    <div class="col">
      <select class="form-select" id="select-file" @change="selectLanguage($event)">
        <option value="" selected>{{ this.trans.translate_select_language }}</option>
        <option v-for="(language, key) in this.languages" v-bind:value="key">{{ language }}</option>
      </select>
    </div>
    <div class="col">
      <!-- <select class="form-select" id="select-time" @change="changeTimeFiltre($event)">
         <option value="">{{ this.trans.log_select_time_all }}</option>
         <option value="now">{{ this.trans.log_select_time_now }}</option>
         <option value="yesterday">{{ this.trans.log_select_time_yesterday }}</option>
       </select>-->
    </div>
  </div>
</template>
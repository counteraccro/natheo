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
    url_translates_files: String,
    url_translate_file: String
  },
  data() {
    return {
      trans: [],
      currentLanguage: '',
      files: [],
      languages: [],
      currentFile: '',
      file: [],
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
        this.loadTranslateListeFile();
      }
    },

    /**
     * Charge la liste de fichier en fonction de la langue
     */
    loadTranslateListeFile() {
      this.files = [];
      axios.post(this.url_translates_files, {
        'language': this.currentLanguage
      }).then((response) => {
        this.files = response.data.files;
      }).catch((error) => {
        console.log(error);
      }).finally();
    },

    /**
     * event du choix du fichier
     * @param event
     */
    selectFile(event) {
      this.currentFile = event.target.value;
      if(this.currentFile!== '')
      {
        this.loadFile();
      }
    },

    loadFile()
    {
      axios.post(this.url_translate_file, {
        'file': this.currentFile
      }).then((response) => {
        this.file = response.data.file;
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
      <select class="form-select" id="select-time" @change="selectFile($event)" :disabled="this.files.length === 0">
         <option value="">{{ this.trans.translate_select_file }}</option>
        <option v-for="(language, key) in this.files" v-bind:value="key">{{ language }}</option>
       </select>
    </div>
  </div>

  <div v-if="this.file.length !== 0">
    <div class="card mt-3">
      <div class="card-header text-bg-secondary">
        {{ this.currentFile }}
      </div>
      <div class="card-body">
          <div v-for="(translate, key) in this.file" class="mb-3 row">
              <label :for="key" class="col-sm-2 col-form-label">{{ key }}</label>
              <div class="col-sm-10">
                  <input v-if="translate.length < 40" type="text" class="form-control" :id="key" :value="translate">
                  <textarea v-else class="form-control" rows="3" :id="key">{{ translate }}</textarea>
              </div>
          </div>
      </div>
    </div>
  </div>
  <div v-else>
    <div class="card mt-3">
      <div class="card-header text-bg-secondary">
        ---
      </div>
      <div class="card-body">
        {{ this.trans.translate_empty_file }}
      </div>
    </div>
  </div>
</template>
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
        url_translate_file: String,
        url_translate_save: String,
    },
    data() {
        return {
            trans: [],
            currentLanguage: '',
            files: [],
            languages: [],
            currentFile: '',
            file: [],
            tabTmpTranslate: [],
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
            this.currentFile = '';
            this.file = [];
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
            if (this.currentFile !== '') {
                this.loadFile();
            }
        },

        /**
         * Charge le contenu du fichier sélectionné
         */
        loadFile() {
            axios.post(this.url_translate_file, {
                'file': this.currentFile
            }).then((response) => {
                this.tabTmpTranslate = [];
                this.file = response.data.file;
            }).catch((error) => {
                console.log(error);
            }).finally();
        },

        /**
         * Sauvegarde les translations modifiées de façon temporaire
         * @param event
         */
        saveTmpTranslate(event) {
            let value = event.target.value;
            let key = event.target.getAttribute('data-id');

            let newE = true;
            for (const i in this.tabTmpTranslate) {
                let element = this.tabTmpTranslate[i];
                if (element.key === key) {
                    element.value = value;
                    newE = false;
                    break;
                }
            }
            if (newE) {
                this.tabTmpTranslate.push({'key': key, 'value': value});
            }
        },

        /**
         * Sauvegarde les traductions modifiées de façon définitive
         */
        saveTranslate() {
            axios.post(this.url_translate_save, {
                'file': this.currentFile,
                'translates': this.tabTmpTranslate
            }).then((response) => {
                //this.file = response.data.file;
            }).catch((error) => {
                console.log(error);
            }).finally();
        },

        /** Défini si la valeur a été changé ou non **/
        isChange(key, type) {
            for (const i in this.tabTmpTranslate) {
                let element = this.tabTmpTranslate[i];
                if (element.key === key) {

                    if(type === 'input')
                    {
                        return "is-update";
                    }
                    return "";

                }
            }
            if(type === 'input') {
                return "";
            }
            return "d-none";
        },

        /** Retourne la bonne valeur **/
        getValue(key, value) {
            for (const i in this.tabTmpTranslate) {
                let element = this.tabTmpTranslate[i];
                if (element.key === key) {
                    return element.value;
                }
            }
            return value;
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
                <span v-if="tabTmpTranslate.length > 0">
                    {{ tabTmpTranslate.length }} traductions changées
                </span>
                <div class="btn btn-success btn-sm float-end" @click="this.saveTranslate">
                    <i class="bi bi-save-fill"></i> {{ this.trans.translate_btn_save }}
                </div>
                <div class="btn btn-warning btn-sm float-end me-2">
                    <i class="bi bi-repeat"></i> {{ this.trans.translate_btn_cache }}
                </div>
            </div>
            <div class="card-body">
                <div v-for="(translate, key) in this.file" class="mb-3 row">
                    <label :for="key" class="col-sm-2 col-form-label">{{ key }}</label>
                    <div class="col-sm-10">
                        <input v-if="translate.length < 40" type="text" class="form-control" :class="this.isChange(key, 'input')" :id="key"
                                :data-id="key" :value="this.getValue(key, translate)" :data-save="translate" @change="this.saveTmpTranslate($event)">
                        <textarea v-else class="form-control" rows="3" :id="key" :data-id="key" :class="this.isChange(key, 'input')"
                                :data-save="translate" @change="this.saveTmpTranslate($event)">{{ this.getValue(key, translate) }}</textarea>
                        <div :data-id="key + '-help'" class="form-text text-warning" :class="this.isChange(key, 'help')">We'll never share your email with anyone else.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else>
        <div class="card mt-3">
            <div class="card-header text-bg-secondary">
                ---
                <div class="btn btn-success btn-sm float-end disabled">
                    <i class="bi bi-save-fill"></i> {{ this.trans.translate_btn_save }}
                </div>
                <div class="btn btn-warning btn-sm float-end me-2 disabled">
                    <i class="bi bi-repeat"></i> {{ this.trans.translate_btn_cache }}
                </div>
            </div>
            <div class="card-body">
                {{ this.trans.translate_empty_file }}
            </div>
        </div>
    </div>
</template>

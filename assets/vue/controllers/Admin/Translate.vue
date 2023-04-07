<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de gérer les traductions de l'application
 */

import axios from "axios";
import {Modal} from "bootstrap";

export default {
    name: "Translate",
    props: {
        url_langue: String,
        url_translates_files: String,
        url_translate_file: String,
        url_translate_save: String,
        url_reload_cache: String,
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
            loading: false,
            modalReloadCache: {},
            isReloadCache: false,
            isReloadCacheFinish: false
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
            this.loading = true;
            axios.post(this.url_translate_file, {
                'file': this.currentFile
            }).then((response) => {
                this.tabTmpTranslate = [];
                this.file = response.data.file;
            }).catch((error) => {
                console.log(error);
            }).finally(() => {
                this.loading = false;
            });
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
            this.loading = true;
            axios.post(this.url_translate_save, {
                'file': this.currentFile,
                'translates': this.tabTmpTranslate
            }).then((response) => {
                this.loadFile();
            }).catch((error) => {
                console.log(error);
            }).finally();
        },

        /**
         * Défini si la valeur a été changé ou non pour l'input
         * @param key
         * @returns {string}
         */
        isChangeInput(key) {

            if (this.isExist(key)) {
                return 'is-update'
            }
            return "";
        },

        /**
         * Défini si l valeur à changé pour l'aide
         * @param key
         * @returns {string}
         */
        isChangeHelp(key) {
            if (this.isExist(key)) {
                return ''
            }
            return "d-none";
        },

        /**
         * Retourne la valeur éditer ou la valeur par défaut
         * @param key
         * @param value
         * @returns {string}
         */
        getValue(key, value) {
            for (const i in this.tabTmpTranslate) {
                let element = this.tabTmpTranslate[i];
                if (element.key === key) {
                    return element.value;
                }
            }
            return value;
        },

        /**
         * Défini si une traduction à été changé ou non
         * @param key
         * @returns {string}
         */
        isExist(key) {
            for (const i in this.tabTmpTranslate) {
                let element = this.tabTmpTranslate[i];
                if (element.key === key) {
                    return true;
                }
            }
            return false;
        },

        /**
         * Supprime une modification dans le tableau temporaire
         * @param key
         */
        revertValue(key) {
            for (const i in this.tabTmpTranslate) {
                let element = this.tabTmpTranslate[i];
                if (element.key === key) {
                    this.tabTmpTranslate.splice(i, 1);
                    break;
                }
            }
            return false;
        },

        /**
         * Permet de recharger le cache
         */
        reloadCache(confirm) {

            if (confirm) {
                this.modalReloadCache = new Modal(document.getElementById("modal-reload-cache"), {});
                this.modalReloadCache.show();
                this.isReloadCache = false;
            } else {
                this.isReloadCache = true;
                axios.post(this.url_reload_cache, {}).then((response) => {

                }).catch((error) => {
                    console.log(error);
                }).finally(() => {
                    this.isReloadCacheFinish = true;
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000)
                });
            }
        }
    }
}
</script>

<template>
    <div class="row">
        <div class="col">
            <select class="form-select no-control" id="select-file" @change="selectLanguage($event)">
                <option value="" selected>{{ this.trans.translate_select_language }}</option>
                <option v-for="(language, key) in this.languages" v-bind:value="key">{{ language }}</option>
            </select>
        </div>
        <div class="col">
            <select class="form-select no-control" id="select-time" @change="selectFile($event)" :disabled="this.files.length === 0">
                <option value="">{{ this.trans.translate_select_file }}</option>
                <option v-for="(language, key) in this.files" v-bind:value="key">{{ language }}</option>
            </select>
        </div>
    </div>

    <div class="modal fade" id="modal-reload-cache" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h1 class="modal-title fs-5 text-white">
                        {{ this.trans.translate_cache_titre }}
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="!this.isReloadCacheFinish">
                        <div v-if="!this.isReloadCache">
                            {{ this.trans.translate_cache_info }}
                        </div>
                        <div v-else>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            {{ this.trans.translate_cache_wait }}
                        </div>
                    </div>
                    <div v-else>
                        <div class="text-success">
                            <i class="bi bi-check-circle-fill"></i> {{ this.trans.translate_cache_success }}</div>
                    </div>
                </div>
                <div class="modal-footer" v-if="!isReloadCacheFinish">
                    <button v-if="!this.isReloadCache" type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ this.trans.translate_cache_btn_close }}</button>
                    <button v-if="!this.isReloadCache" type="button" class="btn btn-primary" @click="this.reloadCache(false)">{{ this.trans.translate_cache_btn_accept }}</button>
                </div>
                <div class="modal-footer" v-else>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ this.trans.translate_cache_btn_close }}</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="this.file.length !== 0">
        <div class="card mt-3" :class="this.loading === true ? 'block-grid' : ''">
            <div v-if="this.loading" class="overlay">
                <div class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="txt-overlay">{{ this.trans.translate_loading }}</span>
                </div>
            </div>
            <div class="card-header text-bg-secondary">
                <div class="btn btn-success btn-sm float-end" @click="this.saveTranslate" :class="this.tabTmpTranslate.length > 0 ? '' : 'disabled'">
                    <i class="bi bi-save-fill"></i> {{ this.trans.translate_btn_save }}
                </div>
                <div class="btn btn-warning btn-sm float-end me-2" @click="this.reloadCache(true)" :class="this.tabTmpTranslate.length === 0 ? '' : 'disabled'">
                    <i class="bi bi-repeat"></i> {{ this.trans.translate_btn_cache }}
                </div>
                <div class="mt-1">
                    {{ this.currentFile }}
                    <span v-if="tabTmpTranslate.length > 0">
                    - <b>{{ tabTmpTranslate.length }}</b> {{ this.trans.translate_nb_edit }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div v-for="(translate, key) in this.file" class="mb-3 row">
                    <label :for="key" class="col-sm-2 col-form-label">{{ key }}</label>
                    <div class="col-sm-10">
                        <input v-if="translate.length < 120" type="text" class="form-control no-control" :class="this.isChangeInput(key)" :id="key"
                                :data-id="key" :value="this.getValue(key, translate)" :data-save="translate" @change="this.saveTmpTranslate($event)">
                        <textarea v-else class="form-control no-control" rows="3" :id="key" :data-id="key" :class="this.isChangeInput(key)"
                                :data-save="translate" @change="this.saveTmpTranslate($event)">{{ this.getValue(key, translate) }}</textarea>
                        <div :data-id="key + '-help'" class="form-text text-warning" :class="this.isChangeHelp(key)">
                            {{ this.trans.translate_info_edit }}
                            <a href="#" onclick="return false;" @click="this.revertValue(key);" class="text-warning">{{ this.trans.translate_link_revert }}</a>
                        </div>
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

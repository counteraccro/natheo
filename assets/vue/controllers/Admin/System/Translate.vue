<script>
/**
 * @author Gourdon Aymeric
 * @version 2.1
 * Permet de gérer les traductions de l'application
 */

import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import Toast from '../../../Components/Global/Toast.vue';
import Modal from '../../../Components/Global/Modal.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';

export default {
  name: 'Translate',
  components: { SkeletonTable, SkeletonText, Modal, Toast },
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
      modalReloadCache: false,
      isReloadCache: false,
      isReloadCacheFinish: false,
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
      },
    };
  },
  mounted() {
    this.loadListeLanguages();
  },
  methods: {
    /**
     * Charge la liste des langues
     */
    loadListeLanguages() {
      this.loading = true;
      axios
        .get(this.url_langue)
        .then((response) => {
          this.languages = response.data.languages;
          this.trans = response.data.trans;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Event de choix de la langue
     * @param event
     */
    selectLanguage(event) {
      this.currentLanguage = event.target.value;
      this.currentFile = '';
      this.file = [];
      if (this.currentLanguage !== '') {
        this.loadTranslateListeFile();
      }
    },

    /**
     * Charge la liste de fichier en fonction de la langue
     */
    loadTranslateListeFile() {
      this.files = [];
      this.loading = true;
      axios
        .get(this.url_translates_files + '/' + this.currentLanguage, {})
        .then((response) => {
          this.files = response.data.files;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
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
      emitter.emit('reset-check-confirm');
      this.loading = true;
      axios
        .get(this.url_translate_file + '/' + this.currentFile)
        .then((response) => {
          this.tabTmpTranslate = [];
          this.file = response.data.file;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
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
        this.tabTmpTranslate.push({ key: key, value: value });
      }
    },

    /**
     * Sauvegarde les traductions modifiées de façon définitive
     */
    saveTranslate() {
      this.loading = true;
      axios
        .put(this.url_translate_save, {
          file: this.currentFile,
          translates: this.tabTmpTranslate,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
            this.loadFile();
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
            this.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally();
    },

    /**
     * Défini si la valeur a été changé ou non pour l'input
     * @param key
     * @returns {string}
     */
    isChangeInput(key) {
      if (this.isExist(key)) {
        return 'is-update';
      }
      return '';
    },

    /**
     * Défini si l valeur à changé pour l'aide
     * @param key
     * @returns {string}
     */
    isChangeHelp(key) {
      if (this.isExist(key)) {
        return '';
      }
      return 'd-none';
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
        this.showModal();
        this.isReloadCache = false;
      } else {
        this.isReloadCache = true;
        axios
          .get(this.url_reload_cache, {})
          .then((response) => {})
          .catch((error) => {
            console.error(error);
          })
          .finally(() => {
            this.isReloadCacheFinish = true;
            setTimeout(() => {
              window.location.reload();
            }, 3000);
          });
      }
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },

    /**
     * Affichage la modale
     */
    showModal() {
      this.modalReloadCache = true;
    },

    /**
     * Ferme la modale
     */
    hideModal() {
      this.modalReloadCache = false;
    },
  },
};
</script>

<template>
  <div class="card rounded-lg p-6 mb-4 mt-4">
    <div v-if="this.loading">
      <SkeletonText :nb-paragraphe="2" />
    </div>

    <div v-else>
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <h2 class="flex gap-2 text-lg font-bold text-[var(--text-primary)]">
          <svg
            class="icon-lg"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 3v4a1 1 0 0 1-1 1H5m8 7.5 2.5 2.5M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Zm-5 9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"
            />
          </svg>

          {{ this.trans.translate_block_search_title }}
        </h2>
        <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">{{ this.trans.translate_block_search_sub_title }}</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
        <div class="form-group">
          <label class="form-label" for="select-file">{{ this.trans.translate_select_language_label }}</label>
          <select
            class="form-input no-control"
            id="select-file"
            @change="selectLanguage($event)"
            v-model="this.currentLanguage"
          >
            <option value="">{{ this.trans.translate_select_language }}</option>
            <option v-for="(language, key) in this.languages" v-bind:value="key">{{ language }}</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="select-time">{{ this.trans.translate_select_file_label }}</label>
          <select
            class="form-input no-control"
            id="select-time"
            @change="selectFile($event)"
            :disabled="this.files.length === 0"
            v-model="this.currentFile"
          >
            <option value="">{{ this.trans.translate_select_file }}</option>
            <option v-for="(language, key) in this.files" v-bind:value="key">{{ language }}</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card rounded-lg p-6 mb-4 mt-4">
    <div v-if="this.loading">
      <SkeletonTable :full="true" />
    </div>
    <div v-else>
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <div class="md:flex md:justify-between">
          <h2 class="flex gap-2 text-lg font-bold text-[var(--text-primary)]">
            <svg
              class="icon-lg"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 3v4a1 1 0 0 1-1 1H5m4 8h6m-6-4h6m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"
              />
            </svg>

            <span v-if="this.file.length !== 0">{{ this.currentFile }}</span>
            <span v-else> --- </span>
          </h2>
          <div v-if="this.file.length !== 0">
            <button class="btn btn-primary btn-sm" @click="this.saveTranslate">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
                ></path>
              </svg>
              {{ this.trans.translate_btn_save }}
            </button>
            <button class="btn btn-dark btn-sm ms-2" @click="this.reloadCache(true)">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                ></path>
              </svg>
              {{ this.trans.translate_btn_cache }}
            </button>
          </div>
        </div>
        <div class="md:flex md:justify-between">
          <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
            {{ this.trans.translate_block_edit_sub_title }}
          </p>
          <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]" v-if="tabTmpTranslate.length > 0">
            <b>{{ tabTmpTranslate.length }}</b> {{ this.trans.translate_nb_edit }}
          </p>
        </div>
      </div>

      <div v-if="this.file.length !== 0">aaaa</div>
      <div v-else>
        <p class="text-center text-[var(--text-secondary)] text-sm italic flex justify-center gap-1">
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ this.trans.translate_empty_file }}
        </p>
      </div>
    </div>
  </div>

  <div :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.trans.translate_loading }}</span>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <select class="form-select no-control" id="select-file2" @change="selectLanguage($event)">
          <option value="">{{ this.trans.translate_select_language }}</option>
          <option v-for="(language, key) in this.languages" v-bind:value="key">{{ language }}</option>
        </select>
      </div>
      <div class="col">
        <select
          class="form-select no-control"
          id="select-time2"
          @change="selectFile($event)"
          :disabled="this.files.length === 0"
        >
          <option value="">{{ this.trans.translate_select_file }}</option>
          <option v-for="(language, key) in this.files" v-bind:value="key">{{ language }}</option>
        </select>
      </div>
    </div>

    <div v-if="this.file.length !== 0">
      <div class="card mt-3 border border-secondary">
        <div class="card-header text-bg-secondary">
          <div class="dropdown">
            <button
              class="btn btn-secondary dropdown-toggle btn-sm float-end"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item no-control" href="#" @click="this.saveTranslate"
                  ><i class="bi bi-save-fill"></i> {{ this.trans.translate_btn_save }}</a
                >
              </li>
              <li>
                <a class="dropdown-item no control" href="#" @click="this.reloadCache(true)">
                  <i class="bi bi-repeat"></i> {{ this.trans.translate_btn_cache }}</a
                >
              </li>
            </ul>
          </div>
          <div class="mt-1">
            <i class="bi bi-translate"></i> {{ this.currentFile }}
            <span v-if="tabTmpTranslate.length > 0">
              - <b>{{ tabTmpTranslate.length }}</b> {{ this.trans.translate_nb_edit }}
            </span>
          </div>
        </div>
        <div class="card-body">
          <div v-for="(translate, key) in this.file" class="mb-3 row">
            <label :for="key" class="col-sm-2 col-form-label">{{ key }}</label>
            <div class="col-sm-10">
              <input
                v-if="translate.length < 120"
                type="text"
                class="form-control"
                :class="this.isChangeInput(key)"
                :id="key"
                :data-id="key"
                :value="this.getValue(key, translate)"
                :data-save="translate"
                @change="this.saveTmpTranslate($event)"
              />
              <textarea
                v-else
                class="form-control"
                rows="3"
                :id="key"
                :data-id="key"
                :class="this.isChangeInput(key)"
                :data-save="translate"
                @change="this.saveTmpTranslate($event)"
                >{{ this.getValue(key, translate) }}</textarea
              >
              <div :data-id="key + '-help'" class="form-text text-warning" :class="this.isChangeHelp(key)">
                {{ this.trans.translate_info_edit }}
                <a href="#" onclick="return false;" @click="this.revertValue(key)" class="text-warning">{{
                  this.trans.translate_link_revert
                }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <div class="card mt-3 border border-secondary">
        <div class="card-header text-bg-secondary">
          <i class="bi bi-translate"></i> ---
          <div class="btn btn-secondary btn-sm float-end disabled">
            <i class="bi bi-list"></i>
          </div>
        </div>
        <div class="card-body">
          {{ this.trans.translate_empty_file }}
        </div>
      </div>
    </div>
  </div>

  <!-- modale refresh cache -->
  <modal
    :id="'modalReloadCache'"
    :show="this.modalReloadCache"
    @close-modal="this.hideModal"
    :option-show-close-btn="false"
    :option-modal-backdrop="'static'"
  >
    <template #title> <i class="bi bi-exclamation-triangle"></i> {{ this.trans.translate_cache_titre }} </template>
    <template #body>
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
        <div class="text-success"><i class="bi bi-check-circle-fill"></i> {{ this.trans.translate_cache_success }}</div>
      </div>
    </template>
    <template #footer>
      <div v-if="!isReloadCacheFinish">
        <button v-if="!this.isReloadCache" type="button" class="btn btn-primary" @click="this.reloadCache(false)">
          <i class="bi bi-check2-circle"></i> {{ this.trans.translate_cache_btn_accept }}
        </button>
        &nbsp;
        <button v-if="!this.isReloadCache" type="button" class="btn btn-secondary" @click="this.hideModal">
          <i class="bi bi-x-circle"></i> {{ this.trans.translate_cache_btn_close }}
        </button>
      </div>
      <div v-else>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          {{ this.trans.translate_cache_btn_close }}
        </button>
      </div>
    </template>
  </modal>
  <!-- fin modale refresh cache -->

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="this.toasts.toastSuccess.show"
      @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.trans.translate_toast_title_success }}</strong>
        <small class="text-black-50">{{ this.trans.translate_toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="this.toasts.toastError.show"
      @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.trans.translate_toast_title_error }}</strong>
        <small class="text-black-50">{{ this.trans.translate_toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

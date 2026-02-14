<script>
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Formulaire pour édition d'un email
 */

import MarkdownEditor from '../../../Components/Global/MarkdownEditor.vue';
import axios from 'axios';
import { Toast } from 'bootstrap';
import { emitter } from '@/utils/useEvent';

export default {
  name: 'Mail',
  components: { MarkdownEditor },
  props: {
    url_data: String,
  },
  data() {
    return {
      translateEditor: {},
      translate: {},
      languages: {},
      currentLanguage: 'fr',
      mail: [],
      loading: false,
      url_save: '',
      url_demo: '',
      isValideTitle: '',
      content: '',
      title: '',
      canSave: true,
      toasts: {
        success: {
          toast: [],
          msg: '',
        },
        error: {
          toast: [],
          msg: '',
        },
      },
    };
  },
  mounted() {
    let toastSuccess = document.getElementById('live-toast-success');
    this.toasts.success.toast = Toast.getOrCreateInstance(toastSuccess);

    let toastError = document.getElementById('live-toast-error');
    this.toasts.error.toast = Toast.getOrCreateInstance(toastError);

    this.loadData();
  },

  methods: {
    /**
     * Récupère les données liées à la gestion des emails
     */
    loadData() {
      this.loading = true;

      axios
        .get(this.url_data + '/' + this.currentLanguage)
        .then((response) => {
          this.translateEditor = response.data.translateEditor;
          this.languages = response.data.languages;
          this.translate = response.data.translate;
          this.currentLanguage = response.data.locale;
          this.mail = response.data.mail;
          this.url_save = response.data.save_url;
          this.url_demo = response.data.demo_url;
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
      if (this.currentLanguage !== '') {
        this.loadData();
      }
    },

    /**
     * Vérifie si on peut sauvegarder ou non
     */
    checkCanSave() {
      this.canSave = this.content !== '' && this.title !== '';
    },

    /**
     * Vérifie si le titre a été saisi ou non
     * @param event
     */
    checkTitle(event) {
      this.isValideTitle = '';
      let value = event.target.value;
      if (value === '') {
        this.isValideTitle = 'is-invalid';
      }
      this.checkCanSave();
    },

    /**
     * Event venant de markdownEditor pour récupérer la valeur saisie
     * @param value
     * @param id
     */
    saveContent(id, value) {
      this.content = value;
      this.checkCanSave();
    },

    /**
     * Mis à jour du titre et contenu
     * @param title
     * @param content
     */
    updateTitleContent(title, content) {
      this.content = content;
      this.title = title;
    },

    /**
     * Permet de sauvegarder le content
     */
    save() {
      if (!this.canSave) {
        this.toasts.error.msg = this.translate.msg_cant_save;
        this.toasts.error.toast.show();
        return false;
      }

      this.loading = true;
      axios
        .post(this.url_save, {
          locale: this.currentLanguage,
          content: this.content,
          title: this.title,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.success.msg = response.data.msg;
            this.toasts.success.toast.show();
          } else {
            this.toasts.error.msg = response.data.msg;
            this.toasts.error.toast.show();
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          emitter.emit('reset-check-confirm');
          this.loading = false;
        });
    },

    /**
     * Permet d'envoyer un email de démo
     */
    sendDemoMail() {
      this.loading = true;
      axios
        .get(this.url_demo)
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.success.msg = response.data.msg;
            this.toasts.success.toast.show();
          } else {
            this.toasts.error.msg = response.data.msg;
            this.toasts.error.toast.show();
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          setTimeout(this.removeMsg, 3000);
          this.loading = false;
        });
    },
  },
};
</script>

<template>
  <div v-if="loading"></div>
  <div v-else>
    <div class="card rounded-lg p-5 mb-5 flex flex-wrap gap-4">
      <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center bg-[var(--primary-lighter)]">
        <svg class="w-5 h-5 text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
          ></path>
        </svg>
      </div>

      <div class="flex-1 min-w-0">
        <p class="font-semibold text-sm text-[var(--text-primary)]">{{ mail.title }}</p>
        <p class="text-sm mt-0.5 text-[var(--text-secondary)]">
          {{ mail.description }}
        </p>
      </div>

      <div class="basis-full lg:basis-auto lg:ml-auto flex items-end gap-2">
        <div>
          <select
            class="form-input form-input-sm no-control"
            id="select-file"
            @change="selectLanguage($event)"
            v-model="currentLanguage"
          >
            <option value="">{{ translate.listLanguage }}</option>
            <option v-for="(language, key) in languages" v-bind:value="key">{{ language }}</option>
          </select>
        </div>
        <div>
          <button class="btn btn-sm btn-primary" @click="save">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
              ></path>
            </svg>
            {{ translate.link_save }}
          </button>
        </div>
        <div>
          <button class="btn btn-sm btn-success" @click="sendDemoMail">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
              />
            </svg>
            {{ translate.link_send }}
          </button>
        </div>
      </div>
    </div>

    <div class="card rounded-lg p-6 mb-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <h2 class="text-lg font-bold text-[var(--text-primary)]">{{ translate.mailContentTitle }}</h2>
        <div class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
          {{ translate.mailContentSubtitle }}
        </div>
      </div>

      <markdown-editor
        :key="mail.key"
        :me-id="String(mail.id)"
        :me-value="mail.contentTrans"
        :me-rows="10"
        :me-translate="translateEditor"
        :me-key-words="mail.keyWords"
        :me-save="false"
        :me-preview="true"
        @editor-value=""
        @editor-value-change="saveContent"
      >
      </markdown-editor>
    </div>
  </div>

  <!-- old

  <div>
    <select
      class="form-select no-control"
      id="select-file"
      @change="selectLanguage($event)"
      v-model="this.currentLanguage"
    >
      <option value="">{{ this.translate.listLanguage }}</option>
      <option v-for="(language, key) in this.languages" v-bind:value="key">{{ language }}</option>
    </select>
  </div>

  <div :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>
    <div v-for="mail in this.mail">
      {{ this.updateTitleContent(mail.titleTrans, mail.contentTrans) }}
      <div class="card mt-2">
        <div class="card-header text-bg-secondary">
          <div class="mt-1 float-start">{{ mail.title }}</div>

          <div class="dropdown">
            <button
              class="btn btn-secondary btn-sm dropdown-toggle float-end"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item no-control" href="#" @click="this.save"
                  ><i class="bi bi-save"></i> {{ this.translate.link_save }}</a
                >
              </li>
              <li>
                <a class="dropdown-item no-control" href="#" @click="this.sendDemoMail"
                  ><i class="bi bi-send-check-fill"></i> {{ this.translate.link_send }}</a
                >
              </li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <p>{{ mail.description }}</p>

          <div class="mb-3">
            <label for="titleTrans" class="form-label">{{ this.translate.titleTrans }}</label>
            <input
              type="text"
              class="form-control"
              :class="this.isValideTitle"
              id="titleTrans"
              v-model="mail.titleTrans"
              @change="this.checkTitle"
            />
            <div id="titleTransError" class="invalid-feedback">
              {{ this.translate.msgEmptyTitle }}
            </div>
          </div>

          <markdown-editor
            :key="mail.key"
            :me-id="String(mail.id)"
            :me-value="mail.contentTrans"
            :me-rows="10"
            :me-translate="translateEditor"
            :me-key-words="mail.keyWords"
            :me-save="false"
            :me-preview="true"
            @editor-value=""
            @editor-value-change="saveContent"
          >
          </markdown-editor>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <div
      id="live-toast-success"
      class="toast border border-secondary bg-white"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
      <div class="toast-header text-success">
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" v-html="this.toasts.success.msg"></div>
    </div>

    <div
      id="live-toast-error"
      class="toast border border-secondary bg-white"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
      <div class="toast-header text-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" v-html="this.toasts.error.msg"></div>
    </div>
  </div> -->
</template>

<style scoped></style>

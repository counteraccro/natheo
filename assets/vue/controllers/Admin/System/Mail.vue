<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Formulaire pour édition d'un email
 */

import { defineComponent, ref } from 'vue';
import MarkdownEditor from '../../../Components/Global/MarkdownEditor/MarkdownEditor.vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import { InternalLinkModule } from '@/ts/MarkdownEditor/modules/internalLink';
import { EditorModule } from '@/ts/MarkdownEditor/MarkdownEditor.types';
import InternalLink from '@/vue/Components/Global/MarkdownEditor/InternalLink.vue';

export default {
  name: 'Mail',
  components: { InternalLink, SkeletonText, SkeletonForm, Toast, MarkdownEditor },
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
      isValideTitle: true,
      canSave: true,
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

  setup() {
    const editorModules: EditorModule[] = [InternalLinkModule];

    return {
      editorModules,
      // ... reste de tes variables
    };
  },

  mounted() {
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
      this.canSave = this.mail.contentTrans !== '' && this.mail.titleTrans !== '';
    },

    /**
     * Vérifie si le titre a été saisi ou non
     * @param event
     */
    checkTitle(event) {
      this.isValideTitle = true;
      let value = event.target.value;
      if (value === '') {
        this.isValideTitle = false;
      }
      this.checkCanSave();
    },

    /**
     * Event venant de markdownEditor pour récupérer la valeur saisie
     * @param value
     * @param id
     */
    saveContent(id, value) {
      console.log(value);
      this.mail.contentTrans = value;
      this.checkCanSave();
    },

    /**
     * Permet de sauvegarder le content
     */
    save() {
      this.checkCanSave();
      if (!this.canSave) {
        this.toasts.toastError.msg = this.translate.msg_cant_save;
        this.toasts.toastError.show = true;
        return false;
      }

      this.loading = true;
      axios
        .post(this.url_save, {
          locale: this.currentLanguage,
          content: this.mail.contentTrans,
          title: this.mail.titleTrans,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
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
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
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

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },
  },
};
</script>

<template>
  <div v-if="loading">
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-text nb-paragraphe="1" />
    </div>
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-form />
    </div>
  </div>
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

      <div class="mb-3">
        <label for="titleTrans" class="form-label">{{ translate.titleTrans }}</label>
        <input
          type="text"
          class="form-input"
          :class="isValideTitle ? '' : 'is-invalid'"
          id="titleTrans"
          v-model="mail.titleTrans"
          @change="checkTitle"
        />
        <div v-if="!isValideTitle" class="form-text text-error">
          {{ translate.msgEmptyTitle }}
        </div>
      </div>

      <div class="mb-3">
        <markdown-editor
          :key="mail.key"
          :me-id="String(mail.id)"
          :me-value="mail.contentTrans"
          :me-rows="10"
          :me-translate="translateEditor"
          :me-key-words="[
            { label: 'Prénom', keyword: '[[user.firstname]]' },
            { label: 'Nom du site', keyword: '[[site.name]]' },
          ]"
          :me-modules="editorModules"
          :me-save="true"
          :me-preview="true"
          @editor-value="saveContent"
          @editor-value-change="saveContent"
        >
        </markdown-editor>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="this.toasts.toastSuccess.show"
      @close-toast="this.closeToast"
    >
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="this.toasts.toastError.show"
      :type="'danger'"
      @close-toast="this.closeToast"
    >
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
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
  </div> -->
</template>

<style scoped></style>

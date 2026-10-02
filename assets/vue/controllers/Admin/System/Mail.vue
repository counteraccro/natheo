<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.1
 * Formulaire pour édition d'un email
 */

import { defineComponent } from 'vue';
import axios from 'axios';
import MarkdownEditor from '@/vue/Components/Global/MarkdownEditor/MarkdownEditor.vue';
import { emitter } from '@/utils/useEvent';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import { InternalLinkModule } from '@/ts/MarkdownEditor/modules/internalLink';
import { MediaModule } from '@/ts/MarkdownEditor/modules/Mediatheque';
import type { EditorModule } from '@/ts/MarkdownEditor/MarkdownEditor.type';
import type { Toasts } from '@/ts/Toast/Toast.type';
import type {
  Mail,
  MailAjaxResponse,
  MailKeyWord,
  MailLoadDataResponse,
  MailPayload,
  MailTranslate,
} from '@/ts/Mail/Mail.type';

export default defineComponent({
  name: 'Mail',
  components: { SkeletonText, SkeletonForm, Toast, MarkdownEditor },

  props: {
    url_data: { type: String, required: true },
    locale: { type: String, required: true },
    csrf_token_save: { type: String, required: true },
    csrf_token_send_demo: { type: String, required: true },
  },

  setup() {
    const editorModules: EditorModule[] = [InternalLinkModule, MediaModule];
    return { editorModules };
  },

  data() {
    return {
      translateEditor: {} as Record<string, Record<string, string>>,
      translate: {} as MailTranslate,
      languages: {} as Record<string, string>,
      currentLanguage: this.locale as string,
      mail: null as Mail | null,
      loading: false as boolean,
      // Requête de sauvegarde / démo en cours, sans démonter le formulaire
      sending: false as boolean,
      url_save: '' as string,
      url_demo: '' as string,
      isValideTitle: true as boolean,
      canSave: true as boolean,
      keyWords: [] as MailKeyWord[],
      toasts: {
        toastSuccess: { show: false, msg: '' },
        toastError: { show: false, msg: '' },
      } as Toasts,
    };
  },

  mounted() {
    this.loadData();
  },

  methods: {
    loadData(): void {
      this.loading = true;

      axios
        .get<MailLoadDataResponse>(this.url_data + '/' + this.currentLanguage)
        .then((response) => {
          this.translateEditor = response.data.translateEditor;
          this.languages = response.data.languages;
          this.translate = response.data.translate;
          this.currentLanguage = response.data.locale;
          this.mail = response.data.mail;
          this.url_save = response.data.save_url;
          this.url_demo = response.data.demo_url;
          this.keyWords = this.convertKeywords(this.mail.keyWords);
          this.isValideTitle = true;
          this.checkCanSave();
        })
        .catch((error: unknown) => {
          this.showError(error, this.translate.msg_error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    selectLanguage(): void {
      this.loadData();
    },

    convertKeywords(raw: Record<string, string>): MailKeyWord[] {
      return Object.entries(raw).map(([key, label]) => ({
        label,
        keyword: `[[${key}]]`,
      }));
    },

    checkCanSave(): void {
      this.canSave = this.mail !== null && this.mail.contentTrans.trim() !== '' && this.mail.titleTrans.trim() !== '';
    },

    checkTitle(): void {
      this.isValideTitle = (this.mail?.titleTrans ?? '').trim() !== '';
      this.checkCanSave();
    },

    saveContent(_id: string, value: string): void {
      if (this.mail === null) {
        return;
      }
      this.mail.contentTrans = value;
      this.checkCanSave();
    },

    getPayload(): MailPayload {
      return {
        locale: this.currentLanguage,
        title: this.mail?.titleTrans ?? '',
        content: this.mail?.contentTrans ?? '',
      };
    },

    save(): void {
      this.checkCanSave();
      if (!this.canSave) {
        this.showToast('toastError', this.translate.msg_cant_save);
        return;
      }

      this.sending = true;
      axios
        .post<MailAjaxResponse>(this.url_save, this.getPayload(), {
          headers: { 'X-CSRF-TOKEN': this.csrf_token_save },
        })
        .then((response) => this.handleResponse(response.data))
        .catch((error: unknown) => this.showError(error, this.translate.msg_cant_save))
        .finally(() => {
          emitter.emit('reset-check-confirm');
          this.sending = false;
        });
    },

    /**
     * Envoie l'email de démo avec le titre et le contenu en cours d'édition (même non sauvegardés)
     */
    sendDemoMail(): void {
      this.sending = true;
      axios
        .post<MailAjaxResponse>(this.url_demo, this.getPayload(), {
          headers: { 'X-CSRF-TOKEN': this.csrf_token_send_demo },
        })
        .then((response) => this.handleResponse(response.data))
        .catch((error: unknown) => this.showError(error, this.translate.msg_error))
        .finally(() => {
          this.sending = false;
        });
    },

    handleResponse(data: MailAjaxResponse): void {
      this.showToast(data.success ? 'toastSuccess' : 'toastError', data.msg);
    },

    /**
     * Affiche le message d'erreur renvoyé par le serveur, sinon le message par défaut
     */
    showError(error: unknown, defaultMsg: string): void {
      console.error(error);
      const msg = axios.isAxiosError<MailAjaxResponse>(error) ? error.response?.data?.msg : undefined;
      this.showToast('toastError', msg || defaultMsg);
    },

    showToast(nameToast: string, msg: string): void {
      this.toasts[nameToast].msg = msg;
      this.toasts[nameToast].show = true;
    },

    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <div v-if="loading">
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-text :nb-paragraphe="1" />
    </div>
    <div class="card rounded-lg p-6 mb-4">
      <skeleton-form />
    </div>
  </div>
  <div v-else-if="mail">
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
            id="select-language"
            :aria-label="translate.listLanguage"
            :title="translate.listLanguage"
            v-model="currentLanguage"
            @change="selectLanguage"
          >
            <option v-for="(language, key) in languages" :key="key" :value="key">{{ language }}</option>
          </select>
        </div>
        <div>
          <button class="btn btn-sm btn-primary" @click="save" :disabled="!canSave || sending">
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
          <button class="btn btn-sm btn-success" @click="sendDemoMail" :disabled="!canSave || sending">
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

    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
            {{ translate.mailContentTitle }}
          </div>
          <p class="card-subtitle">
            {{ translate.mailContentSubtitle }}
          </p>
        </div>
      </div>
      <div class="p-5">
        <div class="mb-3">
          <label for="titleTrans" class="form-label">{{ translate.titleTrans }}</label>
          <input
            type="text"
            class="form-input"
            :class="isValideTitle ? '' : 'is-invalid'"
            id="titleTrans"
            v-model="mail.titleTrans"
            @input="checkTitle"
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
            :me-rows="15"
            :me-translate="translateEditor"
            :me-key-words="keyWords"
            :me-modules="editorModules"
            :me-save="true"
            :me-preview="true"
            :me-required="true"
            @editor-value="saveContent"
            @editor-value-change="saveContent"
          >
          </markdown-editor>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast id="toastSuccess" :show="toasts.toastSuccess.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast id="toastError" type="danger" :show="toasts.toastError.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

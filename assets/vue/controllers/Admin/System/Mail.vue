<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Formulaire pour édition d'un email
 */

import { defineComponent } from 'vue';
import MarkdownEditor from '../../../Components/Global/MarkdownEditor/MarkdownEditor.vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import { InternalLinkModule } from '@/ts/MarkdownEditor/modules/internalLink';
import { EditorModule } from '@/ts/MarkdownEditor/MarkdownEditor.types';
import InternalLink from '@/vue/Components/Global/MarkdownEditor/InternalLink.vue';
import MediathequeModale from '@/vue/Components/Global/MarkdownEditor/Mediatheque.vue';
import { MediaModule } from '@/ts/MarkdownEditor/modules/Mediatheque';

// Types locaux
type KeyWord = {
  label: string;
  keyword: string;
};

type Mail = {
  id: number;
  key: number;
  title: string;
  description: string;
  titleTrans: string;
  contentTrans: string;
  keyWords: Record<string, string>;
};

type Toast = {
  show: boolean;
  msg: string;
};

export default defineComponent({
  name: 'Mail',
  components: { InternalLink, SkeletonText, SkeletonForm, Toast, MarkdownEditor, MediathequeModale },

  props: {
    url_data: { type: String, required: true },
  },

  setup() {
    const editorModules: EditorModule[] = [InternalLinkModule, MediaModule];
    return { editorModules };
  },

  data() {
    return {
      translateEditor: {} as Record<string, Record<string, string>>,
      translate: {} as Record<string, string>,
      languages: {} as Record<string, string>,
      currentLanguage: 'fr' as string,
      mail: {} as Mail,
      loading: false as boolean,
      url_save: '' as string,
      url_demo: '' as string,
      isValideTitle: true as boolean,
      canSave: true as boolean,
      KeyWords: [] as KeyWord[],
      toasts: {
        toastSuccess: { show: false, msg: '' } as Toast,
        toastError: { show: false, msg: '' } as Toast,
      },
    };
  },

  mounted() {
    this.loadData();
  },

  methods: {
    loadData(): void {
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
          this.KeyWords = this.convertKeywords(this.mail.keyWords);
        });
    },

    selectLanguage(event: Event): void {
      const target = event.target as HTMLSelectElement;
      this.currentLanguage = target.value;
      if (this.currentLanguage !== '') {
        this.loadData();
      }
    },

    convertKeywords(raw: Record<string, string>): KeyWord[] {
      return Object.entries(raw).map(([key, label]) => ({
        label,
        keyword: `[[${key}]]`,
      }));
    },

    checkCanSave(): void {
      this.canSave = this.mail.contentTrans !== '' && this.mail.titleTrans !== '';
    },

    checkTitle(event: Event): void {
      this.isValideTitle = true;
      const value = (event.target as HTMLInputElement).value;
      if (value === '') {
        this.isValideTitle = false;
      }
      this.checkCanSave();
    },

    saveContent(_id: string, value: string): void {
      this.mail.contentTrans = value;
      this.checkCanSave();
    },

    save(): void {
      this.checkCanSave();
      if (!this.canSave) {
        this.toasts.toastError.msg = this.translate.msg_cant_save;
        this.toasts.toastError.show = true;
        return;
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

    sendDemoMail(): void {
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
          this.loading = false;
        });
    },

    closeToast(nameToast: keyof typeof this.toasts): void {
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
          <button class="btn btn-sm btn-primary" @click="save" :disabled="!canSave">
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
          :me-rows="15"
          :me-translate="translateEditor"
          :me-key-words="KeyWords"
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

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="toasts.toastSuccess.show"
      @close-toast="closeToast"
    >
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="toasts.toastError.show"
      :type="'danger'"
      @close-toast="closeToast"
    >
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style scoped></style>

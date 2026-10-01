<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.3
 * Permet de gérer les traductions de l'application
 */

import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import SkeletonTable from '@/vue/Components/Skeleton/Table.vue';
import type { Toasts } from '@/ts/Toast/Toast.type';
import type {
  TranslateErrorResponse,
  TranslateFileResponse,
  TranslateFilesResponse,
  TranslateLanguagesResponse,
  TranslateSavePayload,
  TranslateSaveResponse,
  TranslateUpdate,
} from '@/ts/Translate/Translate.type';

export default defineComponent({
  name: 'Translate',
  components: { SkeletonTable, SkeletonText, Toast },
  props: {
    url_langue: { type: String, required: true },
    url_translates_files: { type: String, required: true },
    url_translate_file: { type: String, required: true },
    url_translate_save: { type: String, required: true },
    translate: { type: Object as PropType<Record<string, string>>, required: true },
  },
  data() {
    return {
      currentLanguage: '' as string,
      files: {} as Record<string, string>,
      languages: {} as Record<string, string>,
      currentFile: '' as string,
      file: {} as Record<string, string>,
      tabTmpTranslate: [] as TranslateUpdate[],
      errors: {} as Record<string, string>,
      search: '' as string,
      onlyUntranslated: false as boolean,
      loading: false as boolean,
      toasts: {
        toastSuccess: { show: false, msg: '' },
        toastError: { show: false, msg: '' },
      } as Toasts,
    };
  },
  computed: {
    hasFile(): boolean {
      return Object.keys(this.file).length !== 0;
    },
    hasFiles(): boolean {
      return Object.keys(this.files).length !== 0;
    },
    untranslatedCount(): number {
      return Object.values(this.file).filter((value) => this.isUntranslated(value)).length;
    },
    filteredFile(): Record<string, string> {
      const search = this.search.trim().toLowerCase();
      return Object.fromEntries(
        Object.entries(this.file).filter(([key, value]) => {
          if (this.onlyUntranslated && !this.isUntranslated(value)) {
            return false;
          }
          return (
            search === '' ||
            key.toLowerCase().includes(search) ||
            this.getValue(key, value).toLowerCase().includes(search)
          );
        })
      );
    },
  },
  mounted() {
    this.loadListeLanguages();
  },
  methods: {
    /**
     * Charge la liste des langues
     */
    loadListeLanguages(): void {
      this.loading = true;
      axios
        .get<TranslateLanguagesResponse>(this.url_langue)
        .then((response) => {
          this.languages = response.data.languages;
        })
        .catch((error) => {
          this.showRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Event de choix de la langue
     * @param event
     */
    selectLanguage(event: Event): void {
      this.currentLanguage = (event.target as HTMLSelectElement).value;
      this.currentFile = '';
      this.file = {};
      if (this.currentLanguage !== '') {
        this.loadTranslateListeFile();
      }
    },

    /**
     * Charge la liste de fichier en fonction de la langue
     */
    loadTranslateListeFile(): void {
      this.files = {};
      this.loading = true;
      axios
        .get<TranslateFilesResponse>(this.url_translates_files + '/' + this.currentLanguage)
        .then((response) => {
          this.files = response.data.files;
        })
        .catch((error) => {
          this.showRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * event du choix du fichier
     * @param event
     */
    selectFile(event: Event): void {
      this.currentFile = (event.target as HTMLSelectElement).value;
      if (this.currentFile !== '') {
        this.loadFile();
      }
    },

    /**
     * Charge le contenu du fichier sélectionné
     */
    loadFile(): void {
      emitter.emit('reset-check-confirm');
      this.loading = true;
      axios
        .get<TranslateFileResponse>(this.url_translate_file + '/' + this.currentFile)
        .then((response) => {
          this.tabTmpTranslate = [];
          this.errors = {};
          this.file = response.data.file;
        })
        .catch((error) => {
          this.currentFile = '';
          this.file = {};
          this.showRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Sauvegarde les translations modifiées de façon temporaire
     * @param event
     */
    saveTmpTranslate(event: Event): void {
      const target = event.target as HTMLInputElement | HTMLTextAreaElement;
      const key = target.getAttribute('data-id') ?? '';
      const value = target.value;
      delete this.errors[key];

      const element = this.tabTmpTranslate.find((translate) => translate.key === key);
      if (element) {
        element.value = value;
      } else {
        this.tabTmpTranslate.push({ key: key, value: value });
      }
    },

    /**
     * Sauvegarde les traductions modifiées de façon définitive
     */
    saveTranslate(): void {
      this.loading = true;
      const payload: TranslateSavePayload = { file: this.currentFile, translates: this.tabTmpTranslate };
      axios
        .put<TranslateSaveResponse>(this.url_translate_save, payload)
        .then((response) => {
          if (response.data.success) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
            this.loadFile();
          } else {
            this.errors = response.data.errors;
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
            this.loading = false;
          }
        })
        .catch((error) => {
          this.showRequestError(error);
          this.loading = false;
        });
    },

    /**
     * Défini la classe de l'input selon qu'il est en erreur ou modifié
     * @param key
     */
    isChangeInput(key: string): string {
      if (this.errors[key] !== undefined) {
        return 'is-invalid';
      }
      if (this.isExist(key)) {
        return 'is-warning';
      }
      return '';
    },

    /**
     * Défini si l valeur à changé pour l'aide
     * @param key
     */
    isChangeHelp(key: string): string {
      if (this.isExist(key)) {
        return '';
      }
      return 'hidden';
    },

    /**
     * Retourne la valeur éditer ou la valeur par défaut
     * @param key
     * @param value
     */
    getValue(key: string, value: string): string {
      return this.tabTmpTranslate.find((translate) => translate.key === key)?.value ?? value;
    },

    /**
     * translation:extract préfixe par "_" ou "__" les valeurs à traduire
     * @param value
     */
    isUntranslated(value: string): boolean {
      return value === '' || value.startsWith('_');
    },

    /**
     * Défini si une traduction à été changé ou non
     * @param key
     */
    isExist(key: string): boolean {
      return this.tabTmpTranslate.some((translate) => translate.key === key);
    },

    /**
     * Supprime une modification dans le tableau temporaire
     * @param key
     */
    revertValue(key: string): void {
      this.tabTmpTranslate = this.tabTmpTranslate.filter((translate) => translate.key !== key);
      delete this.errors[key];
    },

    /**
     * Affiche le message d'erreur renvoyé par le serveur, ou un message générique si absent
     * @param error
     */
    showRequestError(error: unknown): void {
      console.error(error);
      const data = axios.isAxiosError<TranslateErrorResponse>(error) ? error.response?.data : undefined;
      this.toasts.toastError.msg = data?.msg ?? this.translate.translate_error_request;
      this.toasts.toastError.show = true;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div v-if="loading">
      <SkeletonText :nb-paragraphe="2" />
    </div>
    <div v-else>
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
              style="color: var(--primary)"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 3v4a1 1 0 0 1-1 1H5m8 7.5 2.5 2.5M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Zm-5 9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"
              />
            </svg>

            {{ translate.translate_block_search_title }}
          </div>
          <p class="card-subtitle">
            {{ translate.translate_block_search_sub_title }}
          </p>
        </div>
      </div>
      <div class="p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
          <div class="form-group">
            <label class="form-label" for="select-file">{{ translate.translate_select_language_label }}</label>
            <select
              class="form-input no-control"
              id="select-file"
              @change="selectLanguage($event)"
              v-model="currentLanguage"
            >
              <option value="">{{ translate.translate_select_language }}</option>
              <option v-for="(language, key) in languages" v-bind:value="key">{{ language }}</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="select-time">{{ translate.translate_select_file_label }}</label>
            <select
              class="form-input no-control"
              id="select-time"
              @change="selectFile($event)"
              :disabled="!hasFiles"
              v-model="currentFile"
            >
              <option value="">{{ translate.translate_select_file }}</option>
              <option v-for="(language, key) in files" v-bind:value="key">{{ language }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div v-if="loading">
      <SkeletonTable :full="true" />
    </div>
    <div v-else>
      <div class="card-header sticky top-0 z-10 bg-white p-6 border-b-1 border-b-[var(--border-color)]">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
              style="color: var(--primary)"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 3v4a1 1 0 0 1-1 1H5m4 8h6m-6-4h6m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"
              />
            </svg>

            <template v-if="hasFile">
              {{ currentFile }}
            </template>
            <template v-else> --- </template>
          </div>

          <p class="card-subtitle">
            {{ translate.translate_block_edit_sub_title }}
          </p>
          <p class="card-subtitle" v-if="tabTmpTranslate.length > 0">
            <b>{{ tabTmpTranslate.length }}</b> {{ translate.translate_nb_edit }}
          </p>
        </div>

        <div class="card-actions" v-if="hasFile">
          <button class="btn btn-primary btn-sm" @click="saveTranslate">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
              ></path>
            </svg>
            {{ translate.translate_btn_save }}
          </button>
        </div>
      </div>

      <div v-if="!hasFile">
        <p class="text-center text-[var(--text-secondary)] text-sm italic flex justify-center gap-1 p-4">
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

          {{ translate.translate_empty_file }}
        </p>
      </div>
      <div v-else>
        <div class="flex flex-col md:flex-row md:items-center gap-4 p-5 border-b-1 border-b-[var(--border-color)]">
          <input
            type="search"
            class="form-input no-control md:max-w-md"
            v-model="search"
            :placeholder="translate.translate_search_placeholder"
            :aria-label="translate.translate_search_placeholder"
          />
          <div class="form-switch form-switch-inline mb-0! md:ml-auto">
            <input
              type="checkbox"
              class="switch-input no-control"
              id="translate-only-untranslated"
              role="switch"
              v-model="onlyUntranslated"
            />
            <label class="switch-toggle" for="translate-only-untranslated"></label>
            <label for="translate-only-untranslated" class="text-sm cursor-pointer">
              {{ translate.translate_filter_untranslated }} ({{ untranslatedCount }})
            </label>
          </div>
        </div>

        <p
          v-if="Object.keys(filteredFile).length === 0"
          class="text-center text-[var(--text-secondary)] text-sm italic p-4"
        >
          {{ translate.translate_search_empty }}
        </p>

        <div
          v-for="(value, key) in filteredFile"
          class="grid grid-cols-1 lg:grid-cols-[30%_1fr] gap-6 p-5 hover:bg-[var(--bg-hover)] transition border-b-1 border-b-[var(--border-color)]"
        >
          <div class="flex items-center">
            <label :for="key" class="font-monospace text-[var(--text-secondary)] text-sm font-medium">{{ key }}</label>
          </div>

          <div>
            <input
              v-if="value.length < 120"
              type="text"
              class="form-input"
              :class="isChangeInput(key)"
              :id="key"
              :data-id="key"
              :value="getValue(key, value)"
              :data-save="value"
              @change="saveTmpTranslate($event)"
            />
            <textarea
              v-else
              class="form-input"
              rows="3"
              :id="key"
              :data-id="key"
              :class="isChangeInput(key)"
              :data-save="value"
              @change="saveTmpTranslate($event)"
              >{{ getValue(key, value) }}</textarea>

            <div v-if="errors[key]" class="form-text text-error mt-2">{{ errors[key] }}</div>

            <div :data-id="key + '-help'" class="form-text text-warning mt-2" :class="isChangeHelp(key)">
              ⚠ {{ translate.translate_info_edit }}
              <a href="#" onclick="return false;" @click="revertValue(key)" class="text-warning float-end no-control">{{
                translate.translate_link_revert
              }}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :show="toasts.toastSuccess.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.toastError.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style>
@keyframes fall-down {
  0% {
    transform: translateY(0);
    opacity: 1;
  }
  80% {
    opacity: 1;
  }
  100% {
    transform: translateY(80px);
    opacity: 0;
  }
}

.falling-file {
  animation: fall-down 2s ease-in infinite;
}
</style>

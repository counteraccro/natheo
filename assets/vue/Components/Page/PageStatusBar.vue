<script lang="ts">
/**
 * Gestionnaire des Page - StatusBar
 * @author Gourdon Aymeric
 * @version 2.0
 */

import { defineComponent, PropType } from 'vue';
import { Locales, Page, PageTranslations, Urls } from '@/ts/Page/type';
import axios from 'axios';
import { initFlowbite } from 'flowbite';

type SectionErrors = Record<string, { hasError: boolean; errorsByLocale: Record<string, Record<string, string>> }>;

export default defineComponent({
  name: 'PageStatusBar',
  props: {
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    sectionErrors: {
      type: Object as PropType<SectionErrors>,
      required: true,
    },
  },
  emits: ['restore-page', 'go-to-error', 'save-page', 'page-preview'],
  data() {
    return {
      autoSaveTimeout: null as ReturnType<typeof setTimeout> | null,
      autoSaveStatus: 'idle' as 'idle' | 'saving' | 'saved' | 'error',
      autoSaveTime: '' as string,
      restoreStatus: 'idle' as 'idle' | 'restored',
      restoreTime: '' as string,
      pageWatchReady: false as boolean,
    };
  },
  mounted() {},
  watch: {
    showErrorSummaryButton(value: boolean) {
      if (value) {
        this.$nextTick(() => {
          initFlowbite();
        });
      }
    },
    page: {
      deep: true,
      handler() {
        if (!this.pageWatchReady) {
          return;
        }

        if (this.hasAnyError) {
          return;
        }

        if (this.autoSaveTimeout) {
          clearTimeout(this.autoSaveTimeout);
        }

        this.autoSaveStatus = 'idle';

        this.autoSaveTimeout = setTimeout(() => {
          this.triggerAutoSave();
        }, 2000);
      },
    },
  },
  computed: {
    /**
     * True ou false si une erreur dans la section content existe
     */
    hasContentError(): boolean {
      return this.sectionErrors.content?.hasError ?? false;
    },

    /**
     * True ou false si une erreur existe
     */
    hasAnyError(): boolean {
      return Object.values(this.sectionErrors).some((section) => section.hasError);
    },

    /**
     * Retourne la liste des sections
     */
    sectionLabels(): Record<string, string> {
      return {
        content: this.translate.onglet_content,
        seo: this.translate.onglet_seo,
      };
    },
    sectionTabIds(): Record<string, string> {
      return {
        content: 'nav-0-tab',
        seo: 'nav-2-tab',
      };
    },

    /**
     * Affiche l'ensemble des erreurs sous forme d'un tableau
     */
    allErrors(): Array<{ section: string; locale: string; message: string }> {
      const result: Array<{ section: string; locale: string; message: string }> = [];

      for (const section of Object.keys(this.sectionErrors)) {
        const errorsByLocale = this.sectionErrors[section]?.errorsByLocale ?? {};
        const sectionLabel = this.sectionLabels[section] ?? section;

        for (const locale of Object.keys(errorsByLocale)) {
          const fieldErrors = errorsByLocale[locale];
          for (const field of Object.keys(fieldErrors)) {
            if (fieldErrors[field] !== '') {
              result.push({
                section,
                locale,
                message: `${sectionLabel} (${this.locales.localesTranslate[locale]}) : ${fieldErrors[field]}`,
              });
            }
          }
        }
      }

      return result;
    },

    /**
     * Retourne l'ensemble des messages d'erreurs
     */
    allErrorMessages(): string[] {
      return this.allErrors.map((error) => error.message);
    },

    /**
     * Affiche le bouton +
     */
    showErrorSummaryButton(): boolean {
      return this.allErrors.length > 1;
    },
  },
  methods: {
    /**
     * Détermine si la page est ready
     */
    notifyPageReady() {
      this.$nextTick(() => {
        this.pageWatchReady = true;
      });
    },

    /**
     * Affiche la notification de page restauré
     */
    notifyRestore() {
      this.pageWatchReady = false;
      this.autoSaveStatus = 'idle';
      this.restoreStatus = 'restored';
      const now = new Date();
      this.restoreTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      setTimeout(() => {
        this.restoreStatus = 'idle';
      }, 3000);
      this.$nextTick(() => {
        this.pageWatchReady = true;
      });
    },

    /**
     * Affichage message d'autosave
     */
    triggerAutoSave() {
      this.autoSaveStatus = 'saving';

      axios
        .put(this.urls.auto_save, { page: this.page })
        .then(() => {
          this.autoSaveStatus = 'saved';
          const now = new Date();
          this.autoSaveTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        })
        .catch(() => {
          this.autoSaveStatus = 'error';
        });
    },

    /**
     * Ce déplace vers l'erreur
     * @param error
     */
    goToError(error: { section: string; locale: string }) {
      this.$emit('go-to-error', error);
    },
  },
});
</script>

<template>
  <div
    class="fixed right-0 bottom-0 left-0 lg:left-64 z-40 shrink-0 px-4 sm:px-6 py-3 flex items-center justify-between bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]"
  >
    <div class="flex items-center gap-2 flex-1 min-w-0">
      <template v-if="!hasAnyError">
        <p
          v-if="autoSaveStatus === 'saving'"
          class="text-xs flex items-center gap-2 text-gray-500 dark:text-gray-400 shrink-0"
        >
          <svg class="w-3 h-3 animate-spin shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          {{ translate.loading }}
        </p>
        <p
          v-else-if="restoreStatus === 'restored'"
          class="text-xs flex items-center gap-2 shrink-0"
          style="color: var(--alert-success-text)"
        >
          <span
            class="w-2 h-2 rounded-full inline-block shrink-0"
            style="background-color: var(--alert-success-text)"
          ></span>
          {{ translate.msg_titre_restore_history }} {{ restoreTime }}
        </p>
        <p
          v-else-if="autoSaveStatus === 'saved'"
          class="text-xs flex items-center gap-2 shrink-0"
          style="color: var(--alert-success-text)"
        >
          <span
            class="w-2 h-2 rounded-full inline-block shrink-0"
            style="background-color: var(--alert-success-text)"
          ></span>
          {{ translate.auto_save_success }} {{ autoSaveTime }}
        </p>
        <p
          v-else-if="autoSaveStatus === 'error'"
          class="text-xs flex items-center gap-2 shrink-0"
          style="color: var(--alert-danger-text)"
        >
          <span class="w-2 h-2 rounded-full inline-block shrink-0" style="background-color: var(--btn-danger)"></span>
          {{ translate.auto_save_error }}
        </p>
        <p v-else class="text-xs flex items-center gap-2 text-gray-500 dark:text-gray-400 shrink-0">
          <span class="w-2 h-2 rounded-full inline-block shrink-0 bg-amber-500"></span>
          Modifications non sauvegardées
        </p>
      </template>

      <div v-else class="text-xs flex items-center gap-2 min-w-0" style="color: var(--alert-danger-text)">
        <span class="w-2 h-2 rounded-full inline-block shrink-0" style="background-color: var(--btn-danger)"></span>
        <button type="button" class="truncate text-left hover:underline" @click="goToError(allErrors[0])">
          {{ allErrorMessages[0] }}
        </button>
        <button
          v-if="showErrorSummaryButton"
          id="errorSummaryButton"
          data-dropdown-toggle="errorSummaryDropdown"
          data-dropdown-placement="top"
          type="button"
          class="shrink-0 underline hover:no-underline"
        >
          (+{{ allErrors.length - 1 }})
        </button>
        <div
          id="errorSummaryDropdown"
          class="z-50 hidden bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700 rounded-lg shadow-lg w-72 border border-gray-200 dark:border-gray-700"
        >
          <ul class="py-2 text-xs">
            <li v-for="(error, index) in allErrors" :key="index">
              <button
                type="button"
                class="w-full flex items-start gap-2 px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                @click="goToError(error)"
              >
                <span
                  class="w-1.5 h-1.5 rounded-full inline-block shrink-0 mt-1"
                  style="background-color: var(--btn-danger)"
                ></span>
                <span class="text-gray-700 dark:text-gray-300">{{ error.message }}</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3 shrink-0">
      <a :href="urls.listing" class="btn btn-outline-dark btn-sm">{{ translate.btn_back }}</a>
      <button class="btn btn-success btn-sm" @click="$emit('page-preview')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2"
          />
        </svg>
        {{ translate.page_save.btn_see_ext }}
      </button>
      <button class="btn btn-primary btn-sm" :disabled="hasAnyError" @click="$emit('save-page')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ translate.page_save.btn_save }}
      </button>
    </div>
  </div>
</template>

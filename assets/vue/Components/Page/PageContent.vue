<script lang="ts">
/**
 * Gestionnaire des Pages - Onglet Content
 * @author Gourdon Aymeric
 * @version 2.0
 */
import { defineComponent, PropType } from 'vue';
import { Locales, Page, PageData, PageTranslationItem, PageTranslations, Urls } from '@/ts/Page/type';
import axios from 'axios';

type PageContentFieldErrors = {
  titre: string;
  url: string;
};

type PageContentErrorsByLocale = Record<string, PageContentFieldErrors>;

export default defineComponent({
  name: 'PageContent',
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    currentLocale: {
      type: String as PropType<string>,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    pageDatas: {
      type: Object as PropType<PageData>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
  },
  data() {
    return {
      urlUniqueCheckResult: {} as Record<string, boolean>,
      urlCheckPending: {} as Record<string, boolean>,
      urlCheckTimeout: null as ReturnType<typeof setTimeout> | null,
      urlWatchReady: false,
      autoSlugEnabled: {} as Record<string, boolean>,
      isAutoSlugging: false,
      headerImg: this.page.headerImg ?? (null as string | null),
      showMediaModal: false,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.urlWatchReady = true;
    });
  },
  emits: ['update-translation', 'update:section-errors', 'update-header-img'],
  computed: {
    /**
     * Slug de l'url
     */
    currentAutoSlugEnabled: {
      get(): boolean {
        return this.autoSlugEnabled[this.currentLocale] ?? false;
      },
      set(value: boolean) {
        this.autoSlugEnabled[this.currentLocale] = value;

        if (value) {
          this.applySlugFromTitre();
        }
      },
    },

    /**
     * Retourne la traduction courante
     */
    currentTranslation(): PageTranslationItem | undefined {
      return this.page.pageTranslations.find((t) => t.locale === this.currentLocale);
    },

    /**
     * setter / getter titre
     */
    currentTitre: {
      get(): string {
        return this.currentTranslation?.titre ?? '';
      },
      set(value: string) {
        this.$emit('update-translation', {
          locale: this.currentLocale,
          field: 'titre',
          value,
        });
      },
    },

    /**
     * Setter / Getter url
     */
    currentUrl: {
      get(): string {
        return this.currentTranslation?.url ?? '';
      },
      set(value: string) {
        this.$emit('update-translation', {
          locale: this.currentLocale,
          field: 'url',
          value,
        });
      },
    },

    /**
     * Vérification si l"url est unique ou non
     */
    isCheckingUrl(): boolean {
      return this.urlCheckPending[this.currentLocale] ?? false;
    },

    /**
     * Gère les erreurs en fonction de la locale
     */
    errorsByLocale(): PageContentErrorsByLocale {
      const result: PageContentErrorsByLocale = {};

      for (const locale of this.locales.locales) {
        const translation = this.page.pageTranslations.find((t) => t.locale === locale);
        const titre = translation?.titre ?? '';
        const url = translation?.url ?? '';

        let urlError = '';
        if (url.trim() === '') {
          urlError = this.translate.msg_error_url_no_unique;
        } else if (false === this.urlUniqueCheckResult[locale]) {
          urlError = this.translate.msg_error_url_no_unique;
        }

        result[locale] = {
          titre: titre.trim() === '' ? this.translate.page_content_form.input_titre_error : '',
          url: urlError,
        };
      }

      return result;
    },

    /**
     * Liste des erreurs en fonction de la locale
     */
    fieldErrors(): PageContentFieldErrors {
      return this.errorsByLocale[this.currentLocale] ?? { titre: '', url: '' };
    },

    /**
     * Une erreur existe ?
     */
    hasError(): boolean {
      return Object.values(this.errorsByLocale).some((localeErrors) =>
        Object.values(localeErrors).some((error) => error !== '')
      );
    },
  },
  watch: {
    errorsByLocale: {
      immediate: true,
      deep: true,
      handler(value: PageContentErrorsByLocale) {
        this.$emit('update:section-errors', {
          section: 'content',
          hasError: this.hasError,
          errorsByLocale: value,
        });
      },
    },
    currentTitre() {
      if (this.currentAutoSlugEnabled) {
        this.applySlugFromTitre();
      }
    },
    currentUrl(newValue: string) {
      if (this.isAutoSlugging) {
        this.isAutoSlugging = false;
      } else if (this.currentAutoSlugEnabled) {
        this.autoSlugEnabled[this.currentLocale] = false;
      }

      if (!this.urlWatchReady) {
        return;
      }

      if (this.urlCheckTimeout) {
        clearTimeout(this.urlCheckTimeout);
      }

      const locale = this.currentLocale;

      if (newValue.trim() === '') {
        delete this.urlUniqueCheckResult[locale];
        this.urlCheckPending[locale] = false;
        return;
      }

      this.urlCheckPending[locale] = true;

      this.urlCheckTimeout = setTimeout(() => {
        this.checkUrlUniqueness(newValue, locale);
      }, 500);
    },
  },
  methods: {
    /**
     * Vérifie si yne url est unique ou non
     * @param url
     * @param locale
     */
    checkUrlUniqueness(url: string, locale: string) {
      const translation = this.page.pageTranslations.find((t) => t.locale === locale);

      axios
        .post(this.urls.is_unique_url_page, { id: translation?.id ?? null, url, locale })
        .then((response) => {
          this.urlUniqueCheckResult[locale] = response.data.is_unique;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.urlCheckPending[locale] = false;
        });
    },

    /**
     * Génère un slug en fonction d'une string
     * @param value
     */
    slugify(value: string): string {
      return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    },

    /**
     * Met à jour l"url en fonction du slug
     */
    applySlugFromTitre() {
      this.isAutoSlugging = true;
      this.currentUrl = this.slugify(this.currentTitre);
    },

    /**
     * Ouvre le gestionnaire de média
     */
    openMediaPicker(): void {
      window.dispatchEvent(
        new CustomEvent('natheo:open-media', {
          detail: {
            onSelect: (media: { url: string }) => {
              this.headerImg = media.url;
              this.$emit('update-header-img', media.url);
            },
          },
        })
      );
    },

    /**
     * Supprime l'image de la page
     */
    removeHeaderImg(): void {
      this.headerImg = null;
      this.$emit('update-header-img', null);
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div class="card-title">
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        {{ translate.page_content_form.title }}
      </div>
    </div>

    <div class="p-5">
      <div class="form-group">
        <label class="form-label">{{ translate.page_content_form.header_img_title }}</label>

        <div v-if="headerImg" class="relative rounded-lg overflow-hidden mb-3" style="height: 160px">
          <img :src="headerImg" class="w-full h-full object-cover" alt="" />
          <div
            class="absolute inset-0 flex items-center justify-center gap-2 opacity-0 hover:opacity-100 transition-opacity"
            style="background-color: rgba(0, 0, 0, 0.45)"
          >
            <button type="button" class="btn btn-sm btn-primary" @click="openMediaPicker">
              {{ translate.page_content_form.header_img_change }}
            </button>
            <button type="button" class="btn btn-sm btn-danger" @click="removeHeaderImg">
              {{ translate.page_content_form.header_img_remove }}
            </button>
          </div>
        </div>

        <div v-else>
          <button
            type="button"
            class="w-full flex flex-col items-center justify-center gap-2 py-8 rounded-lg border-2 border-dashed transition-all cursor-pointer"
            style="border-color: var(--border-dark); color: var(--text-secondary)"
            @mouseenter="($event.currentTarget as HTMLElement).style.borderColor = 'var(--primary)'"
            @mouseleave="($event.currentTarget as HTMLElement).style.borderColor = 'var(--border-dark)'"
            @click="openMediaPicker"
          >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="1.5" />
              <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5" />
              <polyline points="21 15 16 10 5 21" stroke-width="1.5" />
            </svg>
            <span class="text-sm font-medium">{{ translate.page_content_form.header_img_no_img }}</span>
          </button>
        </div>

        <div class="form-text">{{ translate.page_content_form.header_img_help }}</div>
      </div>

      <div class="form-group">
        <label for="list-render-page" class="form-label">{{ translate.page_content_form.list_categories_label }}</label>
        <select id="list-render-page" class="form-input" v-model="page.category">
          <option v-for="(value, key) in pageDatas.list_categories" :value="parseInt(key)">{{ value }}</option>
        </select>
        <div id="list-status-help" class="form-text">{{ translate.page_content_form.list_categories_help }}</div>
      </div>

      <div class="form-group">
        <div class="flex items-center justify-between">
          <label class="form-label">{{ translate.page_content_form.input_titre_label }}</label>
          <div class="flex items-center gap-1.5 text-xs cursor-pointer" style="color: var(--text-secondary)">
            <div class="form-check">
              <input type="checkbox" id="check-auto-slug" v-model="currentAutoSlugEnabled" class="form-check-input" />
              <label class="form-check-label" for="check-auto-slug">{{
                translate.page_content_form.auto_slug_label
              }}</label>
            </div>
          </div>
        </div>
        <input type="text" class="form-input" :class="fieldErrors.titre ? 'is-invalid' : ''" v-model="currentTitre" />
        <div v-if="!fieldErrors.titre" id="list-status-help" class="form-text">
          {{ translate.page_content_form.input_titre_info }}
        </div>
        <div v-if="fieldErrors.titre" class="form-text text-error">✗ {{ fieldErrors.titre }}</div>
      </div>

      <div class="form-group">
        <label class="form-label">{{ translate.page_content_form.input_url_label }}</label>
        <div class="relative">
          <div class="input-addon-group">
            <span class="input-addon input-addon-left">
              <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                ></path>
              </svg>
              {{ pageDatas.list_categories[page.category].toLowerCase() }}/</span
            >
            <input type="text" class="form-input" :class="fieldErrors.url ? 'is-invalid' : ''" v-model="currentUrl" />
          </div>

          <svg
            v-if="isCheckingUrl"
            class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 animate-spin"
            style="color: var(--text-secondary)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            ></path>
          </svg>
        </div>
        <div v-if="!fieldErrors.url" id="list-status-help" class="form-text">
          {{ translate.page_content_form.input_url_info }}
        </div>
        <div v-if="fieldErrors.url" class="form-text text-error">✗ {{ fieldErrors.url }}</div>
      </div>

      <div class="form-group">
        <label for="list-render-page" class="form-label">{{ translate.page_content_form.list_render_label }}</label>
        <select id="list-render-page" class="form-input" v-model="page.render">
          <option v-for="(value, key) in pageDatas.list_render" :value="parseInt(key)">{{ value }}</option>
        </select>
        <div id="list-status-help" class="form-text">{{ translate.page_content_form.list_render_help }}</div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">
      <div class="card-title">
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-width="2"
            d="M5.005 11.19V12l6.998 4.042L19 12v-.81M5 16.15v.81L11.997 21l6.998-4.042v-.81M12.003 3 5.005 7.042l6.998 4.042L19 7.042 12.003 3Z"
          />
        </svg>
        {{ translate.page_content.title }}
      </div>
    </div>
    <div class="p-4">bbb</div>
  </div>
</template>

<style scoped></style>

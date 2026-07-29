<script lang="ts">
/**
 * Gestionnaire des Page - Onglet SEO
 * @author Gourdon Aymeric
 * @version 2.0
 */

import { defineComponent, PropType } from 'vue';
import { Locales, Page, PageSeoTranslate, PageTranslations } from '@/ts/Page/type';

const SEO_FIELDS = ['description', 'keywords', 'author', 'copyright'] as const;
type SeoField = (typeof SEO_FIELDS)[number];

type PageSeoFieldErrors = {
  description: string;
  keywords: string;
  author: string;
  copyright: string;
};

type PageSeoErrorsByLocale = Record<string, PageSeoFieldErrors>;

const SEO_ERROR_KEYS: Record<SeoField, keyof PageSeoTranslate> = {
  description: 'input_meta_description_error',
  keywords: 'input_meta_keywords_error',
  author: 'input_meta_author_error',
  copyright: 'input_meta_copyright_error',
};

export default defineComponent({
  name: 'PageSEO',
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
  },
  emits: ['update-meta', 'update:section-errors'],
  computed: {
    /**
     * Getter / Setter MediaDescription
     */
    currentMetaDescription: {
      get(): string {
        return (
          this.page.pageMetas
            .find((m) => m.name === 'description')
            ?.pageMetaTranslations.find((t) => t.locale === this.currentLocale)?.value ?? ''
        );
      },
      set(value: string) {
        this.$emit('update-meta', { name: 'description', locale: this.currentLocale, value });
      },
    },

    /**
     * Getter / Setter Meta Keywords
     */
    currentMetaKeywords: {
      get(): string {
        return (
          this.page.pageMetas
            .find((m) => m.name === 'keywords')
            ?.pageMetaTranslations.find((t) => t.locale === this.currentLocale)?.value ?? ''
        );
      },
      set(value: string) {
        this.$emit('update-meta', { name: 'keywords', locale: this.currentLocale, value });
      },
    },

    /**
     * Getter / setter meta Author
     */
    currentMetaAuthor: {
      get(): string {
        return (
          this.page.pageMetas
            .find((m) => m.name === 'author')
            ?.pageMetaTranslations.find((t) => t.locale === this.currentLocale)?.value ?? ''
        );
      },
      set(value: string) {
        this.$emit('update-meta', { name: 'author', locale: this.currentLocale, value });
      },
    },

    /**
     * Getter / Setter Copyright
     */
    currentMetaCopyright: {
      get(): string {
        return (
          this.page.pageMetas
            .find((m) => m.name === 'copyright')
            ?.pageMetaTranslations.find((t) => t.locale === this.currentLocale)?.value ?? ''
        );
      },
      set(value: string) {
        this.$emit('update-meta', { name: 'copyright', locale: this.currentLocale, value });
      },
    },

    /**
     * Retourne un tableau d'erreur en fonction de la locale
     */
    errorsByLocale(): PageSeoErrorsByLocale {
      const result: PageSeoErrorsByLocale = {};

      for (const locale of this.locales.locales) {
        const errors: PageSeoFieldErrors = {
          description: '',
          keywords: '',
          author: '',
          copyright: '',
        };

        for (const field of SEO_FIELDS) {
          const meta = this.page.pageMetas.find((m) => m.name === field);
          const translation = meta?.pageMetaTranslations.find((t) => t.locale === locale);
          const value = translation?.value ?? '';

          if (value.trim() === '') {
            errors[field] = this.translate.page_seo[SEO_ERROR_KEYS[field]] ?? '';
          }
        }

        result[locale] = errors;
      }

      return result;
    },

    /**
     * Erreurs
     */
    fieldErrors(): PageSeoFieldErrors {
      return (
        this.errorsByLocale[this.currentLocale] ?? {
          description: '',
          keywords: '',
          author: '',
          copyright: '',
        }
      );
    },

    /**
     * true ou false en fonction si une erreur est présente ou non
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
      handler(value: PageSeoErrorsByLocale) {
        this.$emit('update:section-errors', {
          section: 'seo',
          hasError: this.hasError,
          errorsByLocale: value,
        });
      },
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div class="card-title">
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
        {{ translate.page_seo.title }}
      </div>
    </div>
    <div class="p-5">
      <div class="form-group">
        <label for="meta-description" class="form-label">{{ translate.page_seo.input_meta_description_label }}</label>
        <textarea
          id="meta-description"
          name="meta-description"
          class="form-input"
          :class="fieldErrors.description ? 'is-invalid' : ''"
          rows="3"
          v-model="currentMetaDescription"
        ></textarea>
        <div v-if="fieldErrors.description" class="form-text text-error">✗ {{ fieldErrors.description }}</div>
        <div v-else class="form-text">{{ translate.page_seo.input_meta_description_help }}</div>
      </div>

      <div class="form-group">
        <label for="meta-keywords" class="form-label">{{ translate.page_seo.input_meta_keywords_label }}</label>
        <input
          id="meta-keywords"
          name="meta-keywords"
          type="text"
          class="form-input"
          :class="fieldErrors.keywords ? 'is-invalid' : ''"
          v-model="currentMetaKeywords"
        />
        <div v-if="fieldErrors.keywords" class="form-text text-error">✗ {{ fieldErrors.keywords }}</div>
        <div v-else class="form-text">{{ translate.page_seo.input_meta_keywords_help }}</div>
      </div>

      <div class="form-group">
        <label for="meta-author" class="form-label">{{ translate.page_seo.input_meta_author_label }}</label>
        <input
          id="meta-author"
          name="meta-author"
          type="text"
          class="form-input"
          :class="fieldErrors.author ? 'is-invalid' : ''"
          v-model="currentMetaAuthor"
        />
        <div v-if="fieldErrors.author" class="form-text text-error">✗ {{ fieldErrors.author }}</div>
        <div v-else class="form-text">{{ translate.page_seo.input_meta_author_help }}</div>
      </div>

      <div class="form-group">
        <label for="meta-copyright" class="form-label">{{ translate.page_seo.input_meta_copyright_label }}</label>
        <input
          id="meta-copyright"
          name="meta-copyright"
          type="text"
          class="form-input"
          :class="fieldErrors.copyright ? 'is-invalid' : ''"
          v-model="currentMetaCopyright"
        />
        <div v-if="fieldErrors.copyright" class="form-text text-error">✗ {{ fieldErrors.copyright }}</div>
        <div v-else class="form-text">{{ translate.page_seo.input_meta_copyright_help }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

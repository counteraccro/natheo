<script lang="ts">
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
    };
  },
  emits: ['update-translation', 'update:section-errors'],
  computed: {
    currentTranslation(): PageTranslationItem | undefined {
      return this.page.pageTranslations.find((t) => t.locale === this.currentLocale);
    },
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
    isCheckingUrl(): boolean {
      return this.urlCheckPending[this.currentLocale] ?? false;
    },
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
    fieldErrors(): PageContentFieldErrors {
      return this.errorsByLocale[this.currentLocale] ?? { titre: '', url: '' };
    },
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
    currentUrl(newValue: string) {
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
  },
});
</script>

<template>
  <div class="card rounded-lg relative overflow-visible mb-5">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
      <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 6h16M4 10h16M4 14h16M4 18h16"
        ></path>
      </svg>
      <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
        translate.page_content_form.title
      }}</span>
    </div>

    <div class="p-4">
      <div class="form-group">
        <label for="list-render-page" class="form-label">{{ translate.page_content_form.list_categories_label }}</label>
        <select id="list-render-page" class="form-input" v-model="page.category">
          <option v-for="(value, key) in pageDatas.list_categories" :value="parseInt(key)">{{ value }}</option>
        </select>
        <div id="list-status-help" class="form-text">{{ translate.page_content_form.list_categories_help }}</div>
      </div>

      <div class="form-group">
        <label class="form-label">{{ translate.page_content_form.input_titre_label }}</label>
        <input type="text" class="form-input" :class="fieldErrors.titre ? 'is-invalid' : ''" v-model="currentTitre" />
        <div v-if="!fieldErrors.titre" id="list-status-help" class="form-text">
          {{ translate.page_content_form.input_titre_info }}
        </div>
        <div v-if="fieldErrors.titre" class="form-text text-error">✗ {{ fieldErrors.titre }}</div>
      </div>

      <div class="form-group">
        <label class="form-label">{{ translate.page_content_form.input_url_label }}</label>
        <div class="relative">
          <input type="text" class="form-input" :class="fieldErrors.url ? 'is-invalid' : ''" v-model="currentUrl" />
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
    </div>
  </div>

  <div class="card rounded-lg relative overflow-visible mb-5">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
      <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5.005 11.19V12l6.998 4.042L19 12v-.81M5 16.15v.81L11.997 21l6.998-4.042v-.81M12.003 3 5.005 7.042l6.998 4.042L19 7.042 12.003 3Z"
        ></path>
      </svg>
      <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.page_content.title }}</span>
    </div>
    <div class="p-4">bbb</div>
  </div>
</template>

<style scoped></style>

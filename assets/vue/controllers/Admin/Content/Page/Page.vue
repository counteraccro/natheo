<script lang="ts">
/**
 * Gestionnaire des Page
 * @author Gourdon Aymeric
 * @version 2.0
 */

import { defineComponent, PropType } from 'vue';
import SkeletonPageContent from '@/vue/Components/Skeleton/Page/PageContent.vue';
import SkeletonPageTag from '@/vue/Components/Skeleton/Page/PageTag.vue';
import SkeletonPageHistory from '@/vue/Components/Skeleton/Page/PageHistory.vue';
import SkeletonPageSave from '@/vue/Components/Skeleton/Page/PageSave.vue';
import SkeletonPageSEO from '@/vue/Components/Skeleton/Page/PageSEO.vue';
import { Locales, Page, PageData, PageTranslations, Urls } from '@/ts/Page/type';
import axios from 'axios';
import PageContent from '@/vue/Components/Page/PageContent.vue';
import { initFlowbite } from 'flowbite';
import MediathequeModale from '@/vue/Components/Global/MarkdownEditor/Mediatheque.vue';
import PageHistory from '@/vue/Components/Page/PageHistory.vue';
import PageStatusBar from '@/vue/Components/Page/PageStatusBar.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import { Toasts } from '@/ts/Toast/type';
import PageSeo from '@/vue/Components/Page/PageSeo.vue';
import PageSEO from '@/vue/Components/Page/PageSeo.vue';
import PageTag from '@/vue/Components/Page/PageTag.vue';

export default defineComponent({
  name: 'Page',
  components: {
    PageTag,
    PageSEO,
    PageSeo,
    Toast,
    PageStatusBar,
    PageHistory,
    MediathequeModale,
    PageContent,
    SkeletonPageSEO,
    SkeletonPageSave,
    SkeletonPageHistory,
    SkeletonPageTag,
    SkeletonPageContent,
  },
  props: {
    id: {
      type: Number as PropType<number>,
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
    pageDatas: {
      type: Object as PropType<PageData>,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      currentLocale: '',
      page: {} as Page,
      activeTab: 'content' as string,
      sectionErrors: {} as Record<
        string,
        { hasError: boolean; errorsByLocale: Record<string, Record<string, string>> }
      >,
      toasts: {
        success: { show: false, msg: '' },
        error: { show: false, msg: '' },
      } as Toasts,
    };
  },
  mounted() {
    this.loadPage();
    this.currentLocale = this.locales.current;
    this.$nextTick(() => {
      initFlowbite();
    });
  },
  computed: {
    /**
     * Erreur onglet content
     */
    hasContentError(): boolean {
      return this.sectionErrors.content?.hasError ?? false;
    },
    /**
     * Erreur onglet SEO
     */
    hasSeoError(): boolean {
      return this.sectionErrors.seo?.hasError ?? false;
    },
  },
  methods: {
    /**
     * Chargement de la page
     */
    loadPage() {
      let url = this.urls.load_page;
      if (this.id !== null) {
        url = this.urls.load_page + '/' + this.id;
      }
      this.loading = true;
      axios
        .get(url, {})
        .then((response) => {
          this.page = response.data.page;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          (this.$refs.statusBar as InstanceType<typeof PageStatusBar>)?.notifyPageReady();
        });
    },

    /**
     * Mise à jour données page
     * @param payload
     */
    handleUpdatePageTranslation(payload: { locale: string; field: 'titre' | 'url'; value: string }) {
      const translation = this.page.pageTranslations.find((t) => t.locale === payload.locale);
      if (translation) {
        translation[payload.field] = payload.value;
      } else {
        this.page.pageTranslations.push({
          id: null,
          page: this.page.id,
          locale: payload.locale,
          titre: payload.field === 'titre' ? payload.value : '',
          url: payload.field === 'url' ? payload.value : '',
        });
      }
    },

    /**
     * Mise à jour donnée SEO
     * @param payload
     */
    handleUpdatePageMeta(payload: { name: string; locale: string; value: string }) {
      const meta = this.page.pageMetas.find((m) => m.name === payload.name);

      if (meta) {
        const translation = meta.pageMetaTranslations.find((t) => t.locale === payload.locale);
        if (translation) {
          translation.value = payload.value;
        } else {
          meta.pageMetaTranslations.push({
            id: null,
            pageMeta: meta.id,
            locale: payload.locale,
            value: payload.value,
          });
        }
      } else {
        this.page.pageMetas.push({
          id: null,
          page: this.page.id,
          name: payload.name,
          pageMetaTranslations: [
            {
              id: null,
              pageMeta: null,
              locale: payload.locale,
              value: payload.value,
            },
          ],
        });
      }
    },

    /**
     * Mise à jour image de la page
     * @param url
     */
    handleUpdateHeaderImg(url: string | null) {
      this.page.headerImg = url;
    },

    /**
     * Gestionnaire des erreurs
     * @param payload
     */
    handleSectionErrors(payload: {
      section: string;
      hasError: boolean;
      errorsByLocale: Record<string, Record<string, string>>;
    }) {
      this.sectionErrors[payload.section] = {
        hasError: payload.hasError,
        errorsByLocale: payload.errorsByLocale,
      };
    },

    /**
     * Restauration historique
     * @param page
     * @param msg
     */
    handleRestoreHistory(page: Page, msg: string) {
      (this.$refs.statusBar as InstanceType<typeof PageStatusBar>).notifyRestore();
      this.page = page;
      this.toasts.success.show = true;
      this.toasts.success.msg = msg;
      setTimeout(() => {
        this.toasts.success.show = false;
      }, 3000);
    },

    /**
     * Affichage onglet ou est présent l'erreur
     * @param error
     */
    handleGoToError(error: { section: string; locale: string }) {
      this.currentLocale = error.locale;
      const tabIds: Record<string, string> = {
        content: 'nav-0-tab',
        seo: 'nav-1-tab',
      };
      const tabButton = document.getElementById(tabIds[error.section]);
      tabButton?.click();
    },
  },
});
</script>
<template>
  <SkeletonPageContent v-if="loading" />

  <div v-else-if="Object.keys(page).length === 0">
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
      <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
        style="background-color: var(--primary-lighter)"
      >
        <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 18h8l-2-4-1.5 2-2-4L8 18Zm7-8.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0Z"
          />
        </svg>
      </div>
      <p class="text-lg font-bold mb-2" style="color: var(--text-primary)">{{ translate.page_no_exist_title }}</p>
      <p class="text-sm max-w-xs mb-6" style="color: var(--text-secondary)">{{ translate.page_no_exist_text }}</p>
      <div class="flex items-center gap-3">
        <a :href="urls.listing" class="btn btn-sm btn-outline-dark flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ translate.btn_back }}
        </a>
        <a :href="urls.new_page" class="btn btn-sm btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ translate.btn_new }}
        </a>
      </div>
    </div>
  </div>

  <div v-else>
    <div class="mb-4 mt-4 border-b border-gray-200 dark:border-gray-700" id="nav-tab-option-system">
      <div class="float-right flex items-center" style="margin-top: -0.5rem">
        <div class="input-addon-group">
          <span class="input-addon input-addon-left">
            <svg
              class="icon-sm"
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
                d="m13 19 3.5-9 3.5 9m-6.125-2h5.25M3 7h7m0 0h2m-2 0c0 1.63-.793 3.926-2.239 5.655M7.5 6.818V5m.261 7.655C6.79 13.82 5.521 14.725 4 15m3.761-2.345L5 10m2.761 2.655L10.2 15"
              />
            </svg>
          </span>
          <select id="select-language" class="form-input form-input-sm" style="width: 120px" v-model="currentLocale">
            <option value="" selected>{{ translate.select_locale }}</option>
            <option v-for="(language, key) in locales.localesTranslate" :value="key">{{ language }}</option>
          </select>
        </div>
      </div>

      <ul
        class="flex items-center flex-wrap mb-px text-sm font-medium text-center"
        id="default-styled-tab"
        data-tabs-toggle="#nav-tab-page"
        data-tabs-active-classes="text-[var(--primary)] hover:text-[var(--primary-hover)] border-[var(--primary)] bg-[var(--primary-lighter)]"
        data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
        role="tablist"
      >
        <li class="me-2" role="presentation">
          <button
            class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
            id="nav-0-tab"
            data-tabs-target="#page-content"
            type="button"
            role="tab"
            :aria-controls="translate.onglet_content"
            aria-selected="true"
            @click="activeTab = 'content'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 10h16M4 14h16M4 18h16"
              />
            </svg>
            {{ translate.onglet_content }}
            <span
              v-if="hasContentError"
              class="w-2 h-2 rounded-full"
              style="background-color: var(--btn-danger)"
            ></span>
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
            id="nav-1-tab"
            data-tabs-target="#page-seo"
            type="button"
            role="tab"
            :aria-controls="translate.onglet_seo"
            aria-selected="false"
            @click="activeTab = 'seo'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            {{ translate.onglet_seo }}
            <span v-if="hasSeoError" class="w-2 h-2 rounded-full" style="background-color: var(--btn-danger)"></span>
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
            id="nav-2-tab"
            data-tabs-target="#page-tag"
            type="button"
            role="tab"
            :aria-controls="translate.onglet_tags"
            aria-selected="false"
            @click="activeTab = 'tag'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
              />
            </svg>
            {{ translate.onglet_tags }}
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
            id="nav-3-tab"
            data-tabs-target="#page-history"
            type="button"
            role="tab"
            :aria-controls="translate.onglet_history"
            aria-selected="false"
            @click="activeTab = 'history'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ translate.onglet_history }}
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
            id="nav-4-tab"
            data-tabs-target="#page-save"
            type="button"
            role="tab"
            :aria-controls="translate.onglet_save"
            aria-selected="false"
            @click="activeTab = 'save'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
              />
            </svg>
            {{ translate.onglet_save }}
          </button>
        </li>
      </ul>
    </div>

    <div id="nav-tab-page">
      <div class="" id="page-content" role="tabpanel" aria-labelledby="nav-0-tab">
        <PageContent
          :translate="translate"
          :page="page"
          :current-locale="currentLocale"
          :locales="locales"
          :page-datas="pageDatas"
          :urls="urls"
          @update-translation="handleUpdatePageTranslation"
          @update:section-errors="handleSectionErrors"
          @update-header-img="handleUpdateHeaderImg"
        />
      </div>
      <div class="hidden" id="page-seo" role="tabpanel" aria-labelledby="nav-1-tab">
        <PageSEO
          :translate="translate"
          :page="page"
          :current-locale="currentLocale"
          :locales="locales"
          @update-meta="handleUpdatePageMeta"
          @update:section-errors="handleSectionErrors"
        />
      </div>
      <div class="hidden" id="page-tag" role="tabpanel" aria-labelledby="nav-2-tab">
        <PageTag :translate="translate" />
      </div>
      <div class="hidden" id="page-history" role="tabpanel" aria-labelledby="nav-3-tab">
        <PageHistory
          :id="id"
          :urls="urls"
          :translate="translate.page_history"
          :active="activeTab === 'history'"
          @restore-history="handleRestoreHistory"
        />
      </div>
      <div class="hidden" id="page-save" role="tabpanel" aria-labelledby="nav-4-tab">Save</div>
    </div>
  </div>

  <SkeletonPageSEO />
  <SkeletonPageTag />
  <SkeletonPageSave />

  <PageStatusBar
    ref="statusBar"
    :page="page"
    :urls="urls"
    :locales="locales"
    :translate="translate"
    :section-errors="sectionErrors"
    @go-to-error="handleGoToError"
  />

  <MediathequeModale :url-media="urls.load_media" :translate="translate.page_content_form.mediatheque" />

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :type="'success'" :show="toasts.success.show">
      <template #body>
        <div v-html="toasts.success.msg"></div>
      </template>
    </toast>
    <toast :id="'toastError'" :type="'danger'" :show="toasts.error.show">
      <template #body>
        <div v-html="toasts.error.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style scoped></style>

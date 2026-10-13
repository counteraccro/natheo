<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Structure de la page de front
 */
import { defineComponent, markRaw, type PropType } from 'vue';
import Nav from '@/vue/Components/Front/NatheoHorizon/Nav/Nav.vue';
import Main from '@/vue/Components/Front/NatheoHorizon/Main/Main.vue';
import Footer from '@/vue/Components/Front/NatheoHorizon/Footer/Footer.vue';
import Skeleton from '@/vue/Components/Front/NatheoHorizon/Skeleton.vue';
import { FrontApi } from '@/utils/Front/FrontApi';
import { UtilsFront } from '@/utils/Front/UtilsFront';
import type {
  FrontApiError,
  FrontDatas,
  FrontPage,
  FrontTranslate,
  FrontUrls,
} from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'Structure',
  components: { Skeleton, Footer, Main, Nav },
  props: {
    datas: {
      type: Object as PropType<FrontDatas>,
      required: true,
    },
    urls: {
      type: Object as PropType<FrontUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontTranslate>,
      required: true,
    },
  },
  data() {
    return {
      error: null as FrontApiError | null,
      // Instances non réactives : services sans état à observer
      frontApi: markRaw(new FrontApi(this.urls, this.datas.apiToken, this.datas.userToken)),
      utilsFront: null as UtilsFront | null,
      page: null as FrontPage | null,
    };
  },
  computed: {
    errorLabel(): string {
      if (this.error === null) {
        return '';
      }
      return this.translate.errorApi[String(this.error.code)] ?? this.translate.errorApi.default;
    },

    homeUrl(): string {
      const homes: Record<string, string> = { fr: this.urls.indexFr, en: this.urls.indexEn, es: this.urls.indexEs };
      return homes[this.datas.locale] ?? this.urls.indexFr;
    },
  },
  created() {
    if (this.datas.apiToken === '') {
      this.apiFailure({ code: 0, message: this.translate.errorApi.tokenMissing });
      return;
    }
    this.load();
  },
  methods: {
    /**
     * Chargement des options systèmes puis de la page
     */
    async load(): Promise<void> {
      try {
        const [optionsSystem, response] = await Promise.all([
          this.frontApi.getOptionSystems(),
          this.frontApi.getPageBySlug({ slug: this.datas.slug, locale: this.datas.locale }),
        ]);
        this.utilsFront = markRaw(new UtilsFront(this.datas, optionsSystem));
        this.page = response.page;
        document.title = this.page.title;
      } catch (error) {
        this.apiFailure(error as FrontApiError);
      }
    },

    apiFailure(error: FrontApiError): void {
      this.error = error;
    },
  },
});
</script>

<template>
  <div v-if="utilsFront && page">
    <header>
      <Nav
        v-if="page.menus?.HEADER"
        :utils-front="utilsFront"
        :data="page.menus.HEADER"
        :translate="translate.header"
        :urls="urls"
        :user-info="datas.userInfo"
      />
    </header>
    <main>
      <Main
        :utils-front="utilsFront"
        :page="page"
        :front-api="frontApi"
        :locale="datas.locale"
        :translate="translate.main"
        @api-failure="apiFailure"
      />
    </main>

    <footer class="tracking-wide bg-white px-2 pt-6 pb-6 border-0 rounded-2xl mt-3">
      <Footer
        v-if="page.menus?.FOOTER"
        :translate="translate.footer"
        :urls="urls"
        :data="page.menus.FOOTER"
        :utils-front="utilsFront"
      />
    </footer>
  </div>
  <div v-else>
    <Skeleton />
  </div>

  <div
    v-if="error"
    class="fixed inset-0 z-50 grid place-content-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="front-error-title"
  >
    <div class="w-full max-w-lg rounded-lg bg-white p-12 shadow-lg">
      <h1 id="front-error-title" class="block font-black text-center text-gray-800 text-7xl sm:text-9xl">
        {{ error.code || '!' }}
      </h1>
      <p class="text-center">
        {{ errorLabel }}
      </p>
      <p v-if="error.message !== errorLabel" class="text-center mt-2">
        <span class="bg-red-200 p-1.5 rounded-md">{{ error.message }}</span>
      </p>

      <p class="text-center mt-4">
        <a
          :href="homeUrl"
          class="text-slate-600 hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.errorApi.back }}</a
        >
      </p>
    </div>
  </div>
</template>

<script>
import Header from "../../../Components/Front/NatheoHorizon/Header.vue";
import Nav from "../../../Components/Front/NatheoHorizon/Nav/Nav.vue";
import Main from "../../../Components/Front/NatheoHorizon/Main/Main.vue";
import Footer from "../../../Components/Front/NatheoHorizon/Footer/Footer.vue";
import {AjaxApiRequest} from "../../../../utils/Front/AjaxApiRequest.js";
import Skeleton from "../../../Components/Front/NatheoHorizon/Skeleton.vue";
import {UtilsFront} from "../../../../utils/Front/UtilsFront";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Structure',
  components: {Skeleton, Footer, Main, Nav, Header},
  props: {
    datas: Object,
    urls: Object,
    translate: Object
  },
  emits: [],
  data() {
    return {
      isLoad: {
        page: false,
        optionsSystem: false
      },
      error: {
        isError: false,
        msg: '',
        code: 0,
      },
      ajaxRequest: '',
      utilsFront: '',
      locale: '',
      slug: '',
      optionsSystem: null,
      page: '',

    }
  },
  created() {
    this.ajaxRequest = new AjaxApiRequest(this.urls, this.datas.userToken)
    this.locale = this.datas.locale;
    this.slug = this.datas.slug;
    this.loadOptionSystem();

  },
  mounted() {
  },

  methods: {

    /**
     * Chargement des options Systems
     */
    loadOptionSystem() {
      let success = (data) => {
        this.optionsSystem = data;
        this.utilsFront = new UtilsFront(this.datas, this.optionsSystem);
        this.loadPage();
      }
      let isLoadOk = () => {
        this.isLoad.optionsSystem = true;
      }
      this.ajaxRequest.getOptionSystems(success, this.apiFailure, isLoadOk)
    },

    /**
     * Charge le contenu de la page
     */
    loadPage() {
      let params = {
        'slug': this.slug,
        'locale': this.locale
      };

      let isLoadOk = () => {
      }

      let success = (data) => {
        this.page = data.page;
        document.title = this.page.title;
        this.isLoad.page = true;
      }
      this.ajaxRequest.getPageBySlug(params, success, this.apiFailure, isLoadOk);
    },

    apiFailure(code, msg) {
      this.error.isError = true;
      this.error.msg = msg;
      this.error.code = code;
      this.isLoad.page = false;
    },

    apiLoader(close) {
      if (close) {
        //alert('stop loading');
      } else {
        //alert('run loading');
      }
    },

    apiSuccess(data) {
      console.log('apiSuccess');
      console.log(data);
    }
  }
}
</script>

<template>

  <div v-if="this.isLoad.optionsSystem && this.isLoad.page">
    <header>
      <Nav
          :options-system="this.optionsSystem"
          :utils-front="this.utilsFront"
          :data="this.page.menus.HEADER"
          :translate="this.translate.header"
          :urls="this.urls"
          :user-info="this.datas.userInfo"
      />
    </header>
    <main>
      <Main
          :utils-front="this.utilsFront"
          :page="this.page"
          :ajax-request="this.ajaxRequest"
          :locale="this.locale"
          :translate="this.translate.main"
          @api-failure="this.apiFailure"
          @api-loader="this.apiLoader"
      />
    </main>

    <footer class="tracking-wide bg-white px-2 pt-6 pb-6 border-0 rounded-2xl mt-3">
      <Footer
          :options-system="this.optionsSystem"
          :translate="this.translate.footer"
          :urls="this.urls"
          :data="this.page.menus.FOOTER"
          :utils-front="this.utilsFront"
      />
    </footer>
  </div>
  <div v-else>

    <Skeleton/>

  </div>

  <div v-if="this.error.isError"
       class="fixed inset-0 z-50 grid place-content-center bg-black/50 p-4"
       role="dialog"
       aria-modal="true"
       aria-labelledby="modalTitle"
  >
    <div class="w-full max-w-lg rounded-lg bg-white p-12 shadow-lg">
      <h1 class="block font-black text-center text-gray-800 text-7xl sm:text-9xl">{{ this.error.code }}</h1>
      <p class="text-center">
        {{ this.translate.errorApi[this.error.code] }}
      </p>
      <p class="text-center mt-2">
        <span class="bg-red-200 p-1.5 rounded-md">{{ this.error.msg }}</span>
      </p>

      <p class="text-center mt-4">
        <a :href="this.urls.indexFr" class="text-slate-600 hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">Retour index</a>
      </p>
    </div>
  </div>
</template>
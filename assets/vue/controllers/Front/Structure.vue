<script>
import Header from "../../Components/Front/Header.vue";
import Nav from "../../Components/Front/Nav.vue";
import Main from "../../Components/Front/Main.vue";
import Footer from "../../Components/Front/Footer.vue";
import {AjaxApiRequest} from "../../../utils/Front/AjaxApiRequest.js";
import Skeleton from "../../Components/Front/Skeleton.vue";

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
  },
  emits: [],
  data() {
    return {
      isLoad: {
        page : false,
        optionsSystem: false
      },
      ajaxRequest: '',
      locale: '',
      slug: '',
      optionsSystem: null,

    }
  },
  created() {
    this.ajaxRequest = new AjaxApiRequest(this.urls)
    this.locale = this.datas.locale;
    this.slug =this.datas.slug;
    this.loadOptionSystem();
    this.loadPage();

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
        'slug' : this.slug,
        'locale' : this.locale
      };

      let isLoadOk = () => {
        this.isLoad.page = true;
      }

      let success = (data) => {
        console.log(data)
      }
      this.ajaxRequest.getPageBySlug(params, success, this.apiFailure, isLoadOk);
    },

    apiFailure(msg) {
      alert(msg);
    },

    apiLoader(close) {
      if(close) {
        //alert('stop loading');
      }
      else {
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
  <header class="rounded bg-gray-300">
    <Header/>
  </header>
  <nav class="h-10 rounded bg-gray-300 mt-2">
    <Nav/>
  </nav>
  <main>
    <Main
        :ajax-request="this.ajaxRequest"
        @api-failure="this.apiFailure"
        @api-loader="this.apiLoader"
    />
  </main>

  <footer class="tracking-wide bg-theme-1-100 px-10 pt-12 pb-6">
    <Footer
      :options-system="this.optionsSystem"
    />
  </footer>
  </div>
  <div v-else>

  <Skeleton />

  </div>
</template>
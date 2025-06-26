<script>
import Header from "./Header.vue";
import Nav from "./Nav.vue";
import Main from "./Main.vue";
import Footer from "./Footer.vue";
import {AjaxApiRequest} from "../../../utils/Front/AjaxApiRequest.js";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Structure',
  components: {Footer, Main, Nav, Header},
  props: {
    slug: String,
    urls: Object,
  },
  emits: [],
  data() {
    return {
      ajaxRequest: '',

    }
  },
  created() {
    this.ajaxRequest = new AjaxApiRequest(this.urls)
    this.ajaxRequest.getPageBySlug(this.apiSuccess, this.apiFailure, this.apiLoader);
  },
  mounted() {
  },

  methods: {
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

  <header class="rounded bg-gray-300">
    <Header/>
  </header>
  <nav class="h-10 rounded bg-gray-300 mt-2">
    <Nav/>
  </nav>
  <main>
    <Main
        :ajax-request="this.AjaxRequest"
        @api-failure="this.apiFailure"
        @api-loader="this.apiLoader"
    />
  </main>

  <footer class="h-10 rounded bg-gray-300 mt-2">
    <Footer/>
  </footer>
</template>
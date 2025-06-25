<script>
import Header from "./Header.vue";
import Nav from "./Nav.vue";
import Main from "./Main.vue";
import Footer from "./Footer.vue";
import {AjaxRequest} from "../../../utils/Front/AjaxRequest.js";
import axios from "axios";

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
      AjaxRequest: '',

    }
  },
  created() {
    this.AjaxRequest = new AjaxRequest(this.urls)
    this.AjaxRequest.getPageBySlug();
  },
  mounted() {
  },

  methods: {

  }
}
</script>

<template>

  <header class="rounded bg-gray-300">
    <Header />
  </header>
  <nav class="h-10 rounded bg-gray-300 mt-2">
    <Nav />
  </nav>
  <main>
    <Main :ajax-request="this.AjaxRequest" />
  </main>

  <footer class="h-10 rounded bg-gray-300 mt-2">
    <Footer />
  </footer>
</template>
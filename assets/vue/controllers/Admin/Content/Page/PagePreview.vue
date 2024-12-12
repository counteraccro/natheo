<script>
/**
 * Permet de générer un rendu pour les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {emitter} from "../../../../../utils/useEvent";
import axios from "axios";


export default {
  name: 'PagePreview',
  props: {
    urls: Object,
    datas: Object,
    translate: Object,
  },
  emits: [],
  data() {
    return {
      loading: false,
      page: [],
    }
  },
  mounted() {
    this.load();
  },
  computed: {},
  methods: {

    /**
     * Charge le contenu globale de la page
     */
    load() {
      this.loading = true;
      axios.get(this.urls.apiFindPage, {
        headers: {
          'Authorization': 'Bearer ' + this.datas.token
        }
      }).then((response) => {
        this.page = response.data.data.page
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loadBlockContent();
      });
    },

    /**
     * Charge les block de contenu
     */
    loadBlockContent() {
      this.page.contents.forEach((element, index) => {
        axios.get(this.urls.apiGetContent + '?id=' + element.id, {
          headers: {
            'Authorization': 'Bearer ' + this.datas.token
          }
        }).then((response) => {
          this.page.contents[index]['content'] = response.data.data;
        }).catch((error) => {
          console.error(error);
        }).finally(() => {
          //this.loadBlockContent();
          this.loading = false;
        });
      });
    }
  }
}
</script>

<template>
  <div id="block-preview" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>
    <header>
      Header du site
    </header>
    <nav>
      Navigation
    </nav>
    <main>
      Block
    </main>

    <footer>
      Footer
    </footer>
  </div>

</template>
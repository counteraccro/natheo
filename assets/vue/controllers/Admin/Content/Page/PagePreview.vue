<script>
/**
 * Permet de générer un rendu pour les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {emitter} from "../../../../../utils/useEvent";
import axios from "axios";
import MenuHeader from "../../../../Components/Menu/MenuHeader.vue";
import {marked} from "marked";
import PreviewContent from "../../../../Components/Page/Preview/PreviewContent.vue";


export default {
  name: 'PagePreview',
  components: {
    PreviewContent,
    MenuHeader
  },
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
    marked,

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
      Header
    </header>
    <nav>
      Navigation
    </nav>
    <main>
      <div class="row" v-if="this.page.render <= 3">
        <div v-for="content in this.page.contents" class="col">
          <PreviewContent v-if="content.type === 1"
            :p-value="content.content.content"
          >
          </PreviewContent>
        </div>
      </div>
      <div v-else-if="this.page.render <= 5" class="row">
        <div v-for="content in this.page.contents" class="col-12">
          <PreviewContent v-if="content.type === 1"
              :p-value="content.content.content"
          >
          </PreviewContent>
        </div>
      </div>
      <div v-else-if="this.page.render === 6" class="row">
        <div class="col-12" v-html="this.page.contents[0].content.content"></div>
        <div class="col-6" v-html="this.page.contents[1].content.content"></div>
        <div class="col-6" v-html="this.page.contents[2].content.content"></div>
      </div>
      <div v-else-if="this.page.render === 7" class="row">
        <div class="col-6" v-html="this.page.contents[0].content.content"></div>
        <div class="col-6" v-html="this.page.contents[1].content.content"></div>
        <div class="col-12" v-html="this.page.contents[2].content.content"></div>
      </div>
      <div v-else-if="this.page.render === 8" class="row">
        <div v-for="content in this.page.contents" class="col-6">
          <PreviewContent v-if="content.type === 1"
              :p-value="content.content.content"
          >
          </PreviewContent>
        </div>
      </div>
    </main>

    <footer>
      Footer
    </footer>
  </div>

</template>
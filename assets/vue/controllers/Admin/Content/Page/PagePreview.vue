<script>
/**
 * Permet de générer un rendu pour les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import MenuHeader from "../../../../Components/Menu/MenuHeader.vue";
import {marked} from "marked";
import PreviewContent from "../../../../Components/Page/Preview/PreviewContent.vue";
import Masonry from "masonry-layout";
import Mail from "../../System/Mail.vue";
import PreviewFooter from "../../../../Components/Page/Preview/Menu/PreviewFooter.vue";


export default {
  name: 'PagePreview',
  components: {
    PreviewFooter,
    Mail,
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
      footer: [],
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
    async load() {
      this.loading = true;
      await axios.get(this.urls.apiFindPage, {
        headers: {
          'Authorization': 'Bearer ' + this.datas.token
        }
      }).then((response) => {
        this.page = response.data.data.page
        this.footer = this.page.menus.FOOTER;
        this.loadBlockContent();
      }).catch((error) => {
        console.error(error);
      }).finally(() => {

      });
    },

    /**
     * Charge les block de contenu
     */
    async loadBlockContent() {
      for (const element of this.page.contents) {
        const index = this.page.contents.indexOf(element);
        await axios.get(this.urls.apiGetContent + '?id=' + element.id, {
          headers: {
            'Authorization': 'Bearer ' + this.datas.token
          }
        }).then((response) => {
          this.page.contents[index].content = response.data.data.content;
        }).catch((error) => {
          console.error(error);
        }).finally(() => {
          this.loading = false;
        });
      }
    },

    /**
     * Renvoi un render en fonction de sa position
     * @param position
     * @return {*}
     */
    renderContent(position) {
      return this.page.contents.filter(
          (content) => content.position === position
      )
    },

    /**
     * Retourne le nombre d'itérations en fonction du render
     * @returns {number}
     */
    getNbIteration() {
      switch (this.page.render) {
        case 1:
          return 1;
        case 2:
          return 2;
        case 3:
          return 3;
        case 4:
          return 2;
        case 5:
          return 3;
        case 6:
          return 2;
        case 7:
          return 2;
        case 8:
          return 4;
      }
    },
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
    <div class="row">
    <nav class="col-2">
      Navigation
    </nav>
    <main class="col" id="bock-content-preview">
      <div class="row" v-if="this.page.render <= 3">
        <div v-for="n in this.getNbIteration()" :class="'mb-2 col-' + (12/this.getNbIteration())">
          <div v-for="content in this.renderContent(n)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
      </div>

      <div v-else-if="this.page.render <= 5" class="row">
        <div v-for="n in this.getNbIteration()" class="mb-2 col-12">
          <div v-for="content in this.renderContent(n)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
      </div>

      <div v-else-if="this.page.render === 6" class="row">
        <div class="col-12">
          <div v-for="content in this.renderContent(1)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
        <div class="col-6">
          <div v-for="content in this.renderContent(2)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
        <div class="col-6">
          <div v-for="content in this.renderContent(3)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
      </div>

      <div v-else-if="this.page.render === 7" class="row">
        <div class="col-6">
          <div v-for="content in this.renderContent(1)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
        <div class="col-6">
          <div v-for="content in this.renderContent(2)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
        <div class="col-12">
          <div v-for="content in this.renderContent(3)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
      </div>

      <div v-else-if="this.page.render === 8" class="row">
        <div v-for="n in this.getNbIteration()" class="mb-2 col-6">
          <div v-for="content in this.renderContent(n)">
            <PreviewContent :p-content="content" :translate="this.translate.preview_content"></PreviewContent>
          </div>
        </div>
      </div>
    </main>
    </div>
      <PreviewFooter
          v-if="this.footer"
          :p-menu="this.footer"
          :data="this.datas.site"
      ></PreviewFooter>
      <div v-else>

      </div>
  </div>

</template>
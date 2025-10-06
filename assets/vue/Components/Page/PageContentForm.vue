<script>
/**
 * Formulaire de l'onglet content
 * @author Gourdon Aymeric
 * @version 1.1
 */
import MediaModalMarkdown from '../Mediatheque/MediaModalMarkdown.vue';
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'PageContentForm',
  components: { MediaModalMarkdown },
  props: {
    page: Object,
    urls: Object,
    listRender: Object,
    listCategories: Object,
    translate: Object,
    locale: String,
    tabError: Object,
  },
  emits: ['auto-save', 'is-unique-url'],
  data() {
    return {
      id: 'page-modal-natheotheque',
      currentFolder: [],
      loadingMedia: false,
      modaleMedia: null,
      dataMedia: [],
    };
  },
  mounted() {
    this.modaleMedia = new Modal(document.getElementById(this.getNameModale('modal-markdown-mediatheque')), {});
  },
  computed: {},
  methods: {
    /**
     * Lance l'autoSave
     */
    autoSave() {
      this.$emit('auto-save', this.page);
    },

    /**
     * Vérifie si le champ est en erreur ou non
     * Si c'est le cas, ajoute la class CSS is-
     */
    checkIsError(key, locale) {
      if (this.tabError[key].locales[locale]) {
        return 'is-invalid';
      }
      return '';
    },

    /**
     * Vérification que l'url est unique
     * @param url
     * @param id
     * @param locale
     */
    isUniqueUrl(url, id, locale) {
      this.$emit('is-unique-url', url, id, locale);
    },

    /**
     * Génère la catégorie pour l'url de la page
     * @param key
     */
    renderCategory(key) {
      let category = this.listCategories[key] + '/';
      return category.toLowerCase();
    },

    /**
     * Si l'url est vide, génère l'url en fonction du titre
     */
    generateUrl(title, id, locale) {
      let url = '';
      this.page.pageTranslations.forEach((pageTranslation, index) => {
        if (pageTranslation.locale === this.locale && pageTranslation.url === '') {
          url = title.replaceAll(' ', '-');
          pageTranslation.url = url;
          this.isUniqueUrl(url, id, locale);
        }
      });
    },

    /**
     * Retourne le nom de la modale fusionné avec un identifiant
     * @param name
     * @returns {string}
     */
    getNameModale(name) {
      return name + '-' + this.id;
    },

    /**
     * Ouvre la modale de la médiathèque
     */
    openModalMediatheque() {
      this.loadMedia(0, 'asc', 'created_at');
      this.modaleMedia.show();
    },

    loadMedia(folderId, order, filter) {
      this.loadingMedia = true;
      axios
        .get(this.urls.load_media + '/' + folderId + '/' + order + '/' + filter, {})
        .then((response) => {
          this.dataMedia = response.data.medias;
          this.currentFolder = response.data.currentFolder;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loadingMedia = false;
        });
    },

    /**
     * Ferme la modale de la médiathèque
     */
    closeModalMediatheque() {
      this.modaleMedia.hide();
      this.dataMedia = [];
    },

    selectMedia(name, url, size) {
      this.page.headerImg = url;
      this.closeModalMediatheque();
      this.autoSave();
    },

    /**
     * Supprime le headerImg
     */
    removeMedia() {
      this.page.headerImg = null;
      this.autoSave();
    },
  },
};
</script>

<template>
  <h5>{{ this.translate.title }}</h5>

  <div class="mb-3">
    <label for="list-render-page" class="form-label">{{ this.translate.list_categories_label }}</label>
    <select
      id="list-render-page"
      class="form-select"
      v-model="this.page.category"
      @change="
        this.renderCategory(this.page.category);
        this.autoSave();
      "
    >
      <option v-for="(value, key) in this.listCategories" :value="parseInt(key)">{{ value }}</option>
    </select>
    <div id="list-status-help" class="form-text">{{ this.translate.list_categories_help }}</div>
  </div>

  <div class="mb-3">
    <div v-for="pageTranslation in this.page.pageTranslations">
      <div v-if="pageTranslation.locale === this.locale">
        <label for="page-titre" class="form-label">{{ this.translate.input_titre_label }}</label>
        <input
          type="text"
          class="form-control"
          id="page-titre"
          v-model="pageTranslation.titre"
          @change="this.generateUrl(pageTranslation.titre, pageTranslation.id, pageTranslation.locale)"
        />
        <div id="pageTitreHelp" class="form-text">{{ this.translate.input_titre_info }}</div>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <div v-for="pageTranslation in this.page.pageTranslations">
      <div v-if="pageTranslation.locale === this.locale">
        <label for="page-url" class="form-label">{{ this.translate.input_url_label }}</label>
        <div class="input-group">
          <span class="input-group-text" id="basic-addon3" v-html="this.renderCategory(this.page.category)"></span>
          <input
            type="text"
            class="form-control"
            :class="this.checkIsError('url', pageTranslation.locale)"
            id="page-url"
            v-model="pageTranslation.url"
            @change="this.isUniqueUrl(pageTranslation.url, pageTranslation.id, pageTranslation.locale)"
          />
        </div>
        <div class="invalid-feedback">
          {{ this.tabError.url.msg }}
        </div>
        <div id="pageUrlHelp" class="form-text">{{ this.translate.input_url_info }}</div>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <h6>{{ this.translate.header_img_title }}</h6>

    <div class="row">
      <div class="col-6">
        {{ this.translate.header_img_help }}

        <div class="mt-2">
          <span v-if="this.page.headerImg" class="btn btn-secondary btn-sm me-2" @click="this.removeMedia">
            <i class="bi bi-x"></i> {{ this.translate.header_img_remove }}
          </span>
          <span class="btn btn-secondary btn-sm" @click="this.openModalMediatheque">
            <i class="bi bi-images"></i> {{ this.translate.header_img_change }}
          </span>
        </div>
      </div>
      <div class="col-6">
        <div v-if="this.page.headerImg" class="w-50 img-thumbnail">
          <img :src="this.page.headerImg" class="img-fluid" alt="bbb" />
        </div>
        <div v-else class="w-50 img-thumbnail">{{ this.translate.header_img_no_img }}</div>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <label for="list-render-page" class="form-label">{{ this.translate.list_render_label }}</label>
    <select id="list-render-page" class="form-select" v-model="this.page.render" @change="this.autoSave">
      <option v-for="(value, key) in this.listRender" :value="parseInt(key)">{{ value }}</option>
    </select>
    <div id="list-status-help" class="form-text">{{ this.translate.list_render_help }}</div>
  </div>

  <div
    class="modal fade"
    :id="this.getNameModale('modal-markdown-mediatheque')"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
  >
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <MediaModalMarkdown
          :medias="this.dataMedia"
          :translate="this.translate.mediatheque"
          :current-folder="this.currentFolder"
          :loading="this.loadingMedia"
          @close-modale="this.closeModalMediatheque"
          @select-media="this.selectMedia"
          @load-media="this.loadMedia"
        >
        </MediaModalMarkdown>
      </div>
    </div>
  </div>
</template>

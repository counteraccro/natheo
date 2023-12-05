<script>/**
 * Formulaire de l'onglet content
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PageContentForm',
  props: {
    page: Object,
    listRender: Object,
    translate: Object,
    locale: String,
    tabError: Object
  },
  emits: ['auto-save', 'is-unique-url'],
  data() {
    return {}
  },
  mounted() {

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
      let isError = false;
      let tab = this.tabError[key].locales;

      if (tab.length === 0) {
        return "";
      }
      tab.forEach(function (data) {
        if (data.locale === locale && data.error === true) {
          isError = true;
        }
      })

      if (isError) {
        return 'is-invalid';
      } else {
        return "";
      }

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
     * Si l'url est vide, génère l'url en fonction du titre
     */
    generateUrl(title) {

      this.page.pageTranslations.forEach((pageTranslation, index) => {
        if (pageTranslation.locale === this.locale && pageTranslation.url === '') {
          pageTranslation.url = title.replaceAll(' ', '-');
        }
      });
      this.autoSave();
    }
  }
}

</script>

<template>

  <h5>{{ this.translate.title }}</h5>

  <div class="mb-3">
    <div v-for="pageTranslation in this.page.pageTranslations">
      <div v-if="pageTranslation.locale === this.locale">
        <label for="page-titre" class="form-label">{{ this.translate.input_titre_label }}</label>
        <input type="text" class="form-control" id="page-titre" v-model="pageTranslation.titre" @change="this.generateUrl(pageTranslation.titre)">
        <div id="pageTitreHelp" class="form-text">{{ this.translate.input_titre_info }}</div>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <div v-for="pageTranslation in this.page.pageTranslations">
      <div v-if="pageTranslation.locale === this.locale">
        <label for="page-url" class="form-label">{{ this.translate.input_url_label }}</label>
        <input type="text" class="form-control" :class="this.checkIsError('url', pageTranslation.locale)" id="page-url" v-model="pageTranslation.url" @change="this.isUniqueUrl(pageTranslation.url, pageTranslation.id, pageTranslation.locale)">
        <div class="invalid-feedback">
          {{ this.tabError.url.msg }}
        </div>
        <div id="pageUrlHelp" class="form-text">{{ this.translate.input_url_info }}</div>
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


</template>
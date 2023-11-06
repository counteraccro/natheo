<script>

/**
 * Formulaire de l'onglet content
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PageContentForm',
  props: {
    page: Object,
    translate: Object,
    locale: String
  },
  emits: ['auto-save'],
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
    <label for="page-titre" class="form-label">{{ this.translate.input_titre_label }}</label>
    <div v-for="pageTranslation in this.page.pageTranslations">
      <input v-if="pageTranslation.locale === this.locale" type="text" class="form-control" id="page-titre" v-model="pageTranslation.titre" @change="this.generateUrl(pageTranslation.titre)">
    </div>
    <div id="pageTitreHelp" class="form-text">{{ this.translate.input_titre_info }}</div>
  </div>

  <div class="mb-3">
    <label for="page-url" class="form-label">{{ this.translate.input_url_label }}</label>
    <div v-for="pageTranslation in this.page.pageTranslations">
      <input v-if="pageTranslation.locale === this.locale" type="text" class="form-control" id="page-url" v-model="pageTranslation.url" @change="this.autoSave">
    </div>
    <div id="pageUrlHelp" class="form-text">{{ this.translate.input_url_info }}</div>
  </div>


</template>
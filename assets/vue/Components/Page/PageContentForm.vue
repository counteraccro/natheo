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
    return {
    }
  },
  mounted() {

  },
  computed: {},
  methods: {
    autoSave()
    {
      this.$emit('auto-save', this.page);
    }
  }
}

</script>

<template>

  <div class="mb-3">
    <label for="page-url" class="form-label">{{ this.translate.input_url_label }}</label>
    <div v-for="pageTranslation in this.page.pageTranslations">
      <input v-if="pageTranslation.locale === this.locale" type="text" class="form-control" id="page-url" v-model="pageTranslation.url" @change="this.autoSave">
    </div>
    <div id="pageUrlHelp" class="form-text">{{ this.translate.input_url_info }}</div>
  </div>

  <div class="mb-3">
    <label for="page-titre" class="form-label">{{ this.translate.input_titre_label }}</label>
    <div v-for="pageTranslation in this.page.pageTranslations">
      <input v-if="pageTranslation.locale === this.locale" type="text" class="form-control" id="page-titre" v-model="pageTranslation.titre">
    </div>
    <div id="pageTitreHelp" class="form-text">{{ this.translate.input_titre_info }}</div>
  </div>

</template>
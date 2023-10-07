<script>/**
 * Permet d'ajouter ou éditer une page
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'Page',
  props: {
    url: String,
    translate: Object,
    locales: Object,
    pPage: Object
  },
  emits: [],
  data() {
    return {
      page: this.pPage,
      currentLocale: '',
      currentTab: 'content',
    }
  },
  mounted() {
    this.currentLocale = this.locales.current;
  },
  computed: {},
  methods: {
    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event) {
      this.currentLocale = event.target.value;
    },

    /**
     * Permet de changer d'onglet
     * @param tab
     */
    switchTab(tab)
    {
      this.currentTab = tab;
      this.loadTab();
    },

    /**
     * Charge le contenu de l'onglet courant
     */
    loadTab()
    {
      console.log(this.currentTab);
    }
  }
}


</script>

<template>

  <nav>
    <select class="form-select float-end w-25" @change="this.switchLocale($event)">
      <option value="" selected>{{ this.translate.select_locale }}</option>
      <option v-for="(language, key) in this.locales.localesTranslate" :value="key" :selected="key===this.currentLocale">{{ language }}</option>
    </select>
    <div class="nav nav-pills mb-3" id="nav-tab-option-system" role="tablist">
      <button class="nav-link active" @click="this.switchTab('content')" id="content-tab" data-bs-toggle="tab" data-bs-target="#nav-content" type="button" role="tab" aria-selected="true">
        {{ this.translate.onglet_content }}
      </button>
      <button class="nav-link" @click="this.switchTab('seo')" id="seo-tab" data-bs-toggle="tab" data-bs-target="#nav-seo" type="button" role="tab" aria-selected="false" tabindex="-1">
        {{ this.translate.onglet_seo }}
      </button>
      <button class="nav-link" @click="this.switchTab('tags')" id="tags-tab" data-bs-toggle="tab" data-bs-target="#nav-tags" type="button" role="tab" aria-selected="false" tabindex="-1">
        {{ this.translate.onglet_tags }}
      </button>
    </div>
  </nav>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="nav-content" role="tabpanel" aria-labelledby="home-tab" tabindex="0">Tab0</div>
    <div class="tab-pane fade" id="nav-seo" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">Tab1</div>
    <div class="tab-pane fade" id="nav-tags" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">Tab2</div>
  </div>

</template>
<script>/**
 * Permet d'ajouter ou éditer une page
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PageForm',
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
    }
  },
  mounted() {
    this.currentLocale = this.locales.current;
  },
  computed: {},
  methods: {
    /**
     * Permet de changer la locale pour la création d'une page
     * @param event
     */
    switchLocale(event) {
      this.currentLocale = event.target.value;
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
      <button class="nav-link active" id="nav-0-tab" data-bs-toggle="tab" data-bs-target="#nav-0" type="button" role="tab" aria-selected="true">
        {{ this.translate.onglet_content }}
      </button>
      <button class="nav-link" id="nav-1-tab" data-bs-toggle="tab" data-bs-target="#nav-1" type="button" role="tab" aria-selected="false" tabindex="-1">
        {{ this.translate.onglet_seo }}
      </button>
      <button class="nav-link " id="nav-2-tab" data-bs-toggle="tab" data-bs-target="#nav-2" type="button" role="tab" aria-selected="false" tabindex="-1">
        {{ this.translate.onglet_tags }}
      </button>
    </div>
  </nav>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="nav-0" role="tabpanel" aria-labelledby="home-tab" tabindex="0">Tab0</div>
    <div class="tab-pane fade" id="nav-1" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">Tab1</div>
    <div class="tab-pane fade" id="nav-2" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">Tab2</div>
  </div>

</template>
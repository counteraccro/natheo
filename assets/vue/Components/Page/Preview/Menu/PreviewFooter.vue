<script>/**
 * Permet d'afficher la préview du footer
 * @author Gourdon Aymeric
 * @version 1.0
 */


export default {
  name: 'PreviewFooter',
  props: {
    pMenu: Object,
    data: Object
  },
  emits: [],
  data() {
    return {}
  },
  methods: {
    /**
     * Retourne l'année courante
     * @return {number}
     */
    getCurrentYear() {
      return new Date().getFullYear();
    },
  }

}
</script>

<template>
  <!-- Menu footer 1 ligne à droite TYPE_FOOTER_1_ROW_RIGHT !-->
  <div class="container" v-if="this.pMenu.type===17">
    <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
      <p class="col-md-4 mb-0 text-body-secondary">© <span v-html="this.getCurrentYear()"></span> {{ this.data.name }}
      </p>

      <a :href="this.data.url" class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <i class="bi" :class="this.data.logo"></i>&nbsp;{{ this.data.name }}
      </a>

      <!--<ul class="nav col-md-4 justify-content-end">
        <li v-for="(element) in this.menu.menuElements" class="nav-item" :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
          <a v-if="!element.disabled" class="nav-link px-2 text-body-secondary"
              :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}
          </a>
        </li>
      </ul>-->
    </footer>
  </div>

  <!-- Menu footer 1 ligne centrée TYPE_FOOTER_1_ROW_CENTER !-->
  <div class="container" v-if="this.pMenu.type===18">
    TYPE_FOOTER_1_ROW_CENTER
  </div>

  <!-- Menu Footer TYPE_FOOTER_COLONNES !-->
  <div class="container" v-if="this.pMenu.type===16">
    TYPE_FOOTER_COLONNES
  </div>
</template>
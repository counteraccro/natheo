<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant header pour la création /edition d'un menu
 */
import translate from "../../controllers/Admin/System/Translate.vue";

export default {
  name: "MenuHeader",
  computed: {
    translate() {
      return translate
    }
  },
  components: {},
  emit: [],
  props: {
    menu: Object,
    type: Number,
    data: Object,
    locale: String,
  },
  watch: {
    type: 'switchType',
    locale: 'switchType'
  },
  data() {
    return {
      demoMenu: [],
    }
  },
  mounted() {

  },
  methods: {

    /**
     * Permet de déterminer le type de rendu
     */
    switchType() {
      this.demoMenu = [];
      switch (this.type) {
        case 1 :
          console.log('TYPE_HEADER_SIDE_BAR');
          //this.generateHeaderSiteBar();
          break;
        case 2 :
          console.log('TYPE_HEADER_MENU_DEROULANT');
          break;
        case 3 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_1_COLONNE');
          break;
        case 4 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_2_COLONNES');
          break;
        case 5 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_3_COLONNES');
          break;
        case 6 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_4_COLONNES');
          break;
        case 7 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_1_COLONNE');
          break;
        case 8 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_2_COLONNES');
          break;
        case 9 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_3_COLONNES');
          break;
        case 10 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_4_COLONNES');
          break;
      }

      console.log(this.demoMenu);
    },

    generateHeaderSiteBar() {
      this.menu.menuElements.forEach((menuElement) => {
        let element = [];
        element = this.getTranslationByLocale(menuElement.menuElementTranslations, element, this.locale);

        if (menuElement.hasOwnProperty('children') && menuElement.children.length > 0) {
          element = this.addChildInElement(menuElement, element);
        }

        this.demoMenu.push(element);
      })
    },

    addChildInElement(menuElement, element) {

      element['children'] = [];

      menuElement.children[0].menuElements.forEach((menuChildren) => {

        //console.log('menuchildren');
        //console.log(menuChildren);

        let children = [];
        children = this.getTranslationByLocale(menuChildren.menuElementTranslations, element, this.locale);

        if (menuChildren.hasOwnProperty('children') && menuChildren.children.length > 0) {
          //return this.addChildInElement(menuChildren, children);
        }
        element.children.push(children);

      })
      return element
    },

    /**
     * Retourne le lien avec la traduction en fonction de la locale
     * @param tabMenuElementTranslation
     */
    getTranslationByLocale(tabMenuElementTranslation) {

      let element = ['text', 'link'];

      tabMenuElementTranslation.forEach((menuElementTranslation) => {
        if (menuElementTranslation.locale === this.locale) {
          element['text'] = menuElementTranslation.textLink;
          element['link'] = menuElementTranslation.link;
        }
      })
      return element;
    }

  }
}
</script>

<template>

  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Navbar</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav" v-if="this.type === 1">
          <li v-for="(element) in this.menu.menuElements" :set="toto = this.getTranslationByLocale(element.menuElementTranslations)">
            <a class="nav-link" :href="toto.link">{{ toto.text }}</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

</template>
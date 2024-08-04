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
  emit: ['reset-dropdown'],
  props: {
    menu: Object,
    type: Number,
    data: Object,
    locale: String,
  },
  watch: {
    //type: 'switchType',
    //locale: 'switchType'
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
    },

    /**
     * Retourne le lien avec la traduction en fonction de la locale
     * @param tabMenuElementTranslation
     */
    getTranslationByLocale(tabMenuElementTranslation) {

      let element = ['text', 'link'];
      //console.log(tabMenuElementTranslation);

      tabMenuElementTranslation.forEach((menuElementTranslation) => {
        if (menuElementTranslation.locale === this.locale) {
          element['text'] = menuElementTranslation.textLink;
          element['link'] = menuElementTranslation.link;
        }
      })
      return element;
    },

    /**
     *  Détermine si le menuElement possède des enfants ou non
     * @param menuElement
     */
    isHaveChildren(menuElement) {
      return menuElement.hasOwnProperty('children');
    },

    /**
     * Génère les dropdown
     * @param menuElement
     * @param html
     * @returns {*}
     */
    renderDeepDropDown(menuElement, html) {

      menuElement.children.forEach((element) => {
        let elementTranslate = this.getTranslationByLocale(element.menuElementTranslations);

        if (!element.disabled) {
          if (!this.isHaveChildren(element)) {
            html += '<li><a class="dropdown-item" href="">' + elementTranslate.text + '</a></li>'
          } else {
            html += '<li class="dropdown dropend">' +
                '<a class="dropdown-item dropdown-toggle no-control" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' + elementTranslate.text + '</a>\n' +
                '<ul class="dropdown-menu">';
            html = this.renderDeepDropDown(element, html);
            html += '</ul>' +
                '</li>';
          }
        }
      })
      return html;
    }

  }
}
</script>

<template>

  <nav class="navbar navbar-expand-lg bg-body-tertiary" id="navbar-header-demo">
    <div class="container-fluid">
      <a class="navbar-brand" :href="this.data.url_site"><i class="bi" :class="this.data.logo"></i> {{ this.data.name }}</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">

        <!-- TYPE_HEADER_SIDE_BAR -->
        <ul class="navbar-nav" v-if="this.type === 1">
          <li v-for="(element) in this.menu.menuElements" class="nav-item"
              :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
            <a v-if="!element.disabled" class="nav-link" :href="elementTranslate.link">{{ elementTranslate.text }}</a>
          </li>
        </ul>

        <!-- TYPE_HEADER_MENU_DEROULANT -->
        <ul class="navbar-nav" v-if="this.type === 2">
          <li v-for="(element) in this.menu.menuElements" class="nav-item" :class="this.isHaveChildren(element) === true ? 'dropdown' : '' "
              :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
            <a v-if="!this.isHaveChildren(element) && !element.disabled" class="nav-link" :href="elementTranslate.link">{{ elementTranslate.text }}</a>
            <a v-else-if="!element.disabled" class="nav-link dropdown-toggle no-control" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ elementTranslate.text }}
            </a>
            <ul v-if="this.isHaveChildren(element)" class="dropdown-menu" v-html="this.renderDeepDropDown(element, '')">

            </ul>
          </li>
        </ul>

        <ul class="navbar-nav" v-if="this.type > 2">
          <li v-for="(element) in this.menu.menuElements" class="nav-item" :class="this.isHaveChildren(element) === true ? 'dropdown has-megamenu' : '' "
              :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
            <a v-if="!this.isHaveChildren(element) && !element.disabled" class="nav-link" :href="elementTranslate.link">{{ elementTranslate.text }}</a>
            <a v-else-if="!element.disabled" class="nav-link dropdown-toggle no-control" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ elementTranslate.text }}
            </a>
            <div v-if="this.isHaveChildren(element)" class="dropdown-menu megamenu" role="menu">
              aaa
            </div>
          </li>

          <li class="nav-item dropdown has-megamenu">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"> Mega menu </a>
            <div class="dropdown-menu megamenu" role="menu">
              <div class="row">
                <div class="col">
                  <ul class="list-unstyled">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                  </ul>
                </div>
                <div class="col">
                  <ul class="list-unstyled">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                  </ul>
                </div>
              </div>
            </div> <!-- dropdown-mega-menu.// -->
          </li>

        </ul>

      </div>
    </div>
  </nav>


</template>
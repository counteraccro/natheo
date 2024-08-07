<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant header pour la création /edition d'un menu
 */
import translate from "../../controllers/Admin/System/Translate.vue";
import {MenuElementTools} from "../../../utils/Admin/Content/Menu/MenuElementsTools";

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
  data() {
    return {
      demoMenu: [],
    }
  },
  mounted() {
  },
  methods: {


    /**
     * Retourne le lien avec la traduction en fonction de la locale
     * @param tabMenuElementTranslation
     */
    getTranslationByLocale(tabMenuElementTranslation) {
      return MenuElementTools.getTranslationByLocale(tabMenuElementTranslation, this.locale);
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
            html += '<li><a class="dropdown-item" target="' + element.linkTarget + '" href="' + elementTranslate.link + '">' + elementTranslate.text + '</a></li>'
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
    },

    /**
     * Génère le rendu des bigs menus
     * @param menuElement
     * @param html
     */
    renderBigMenu(menuElement, html) {
      let column = 0;
      let row = 0;
      let col = this.getColNumberByType();
      menuElement.children.forEach((element, key) => {

        let elementTranslate = this.getTranslationByLocale(element.menuElementTranslations);
        if (!element.disabled) {

          if (column !== element.columnPosition) {
            column = element.columnPosition;
            row = element.rowPosition;
            html += '<div class="' + col + '"><ul class="list-unstyled">';
          }

          if (row !== element.rowPosition) {
            row = element.rowPosition;
            html += '<li><hr class="dropdown-divider"></li>';
          }

          html += '<li><a class="dropdown-item"  target="' + element.linkTarget + '" href="' + elementTranslate.link + '">' + elementTranslate.text + '</a>';
          if (this.isHaveChildren(element)) {
            html += '<ul class="list-custom">';
            html = this.renderBigMenuDeep(element, html);
            html += '</ul>';
          }
          html += '</li>';

          if (menuElement.children[key + 1] === undefined || menuElement.children[key + 1].columnPosition !== column) {
            html += '</ul></div>';
          }
        }
      });
      return html;
    },

    /**
     * Génère les profondeurs de deepMenu
     * @param menuElement
     * @param html
     * @return string
     */
    renderBigMenuDeep(menuElement, html) {

      menuElement.children.forEach((element) => {
        let elementTranslate = this.getTranslationByLocale(element.menuElementTranslations);
        if (!element.disabled) {
          html += '<li><a class="dropdown-item"  target="' + element.linkTarget + '" href="' + elementTranslate.link + '">' + elementTranslate.text + '</a>'
          if (this.isHaveChildren(element)) {
            html += '<ul class="list-custom">';
            html = this.renderBigMenuDeep(element, html);
            html += '</ul>';
          }
          html += '</li>';
        }
      });

      return html;
    },

    /**
     * Génère le bon nombre de colonnes en fonction du type
     * @return {string}
     */
    getColNumberByType() {
      let col = '';
      switch (this.type) {
        case 3 : // TYPE_HEADER_MENU_DEROULANT_BIG_MENU
          col = 'col';
          break;
        case 4 : // TYPE_HEADER_MENU_DEROULANT_BIG_MENU_2_COLONNES
          col = 'col-6';
          break;
        case 5 : // TYPE_HEADER_MENU_DEROULANT_BIG_MENU_3_COLONNES
          col = 'col-4';
          break;
        case 6 : // TYPE_HEADER_MENU_DEROULANT_BIG_MENU_4_COLONNES
          col = 'col-3';
          break;
        default :
          col = 'col';
      }
      return col;
    },
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
            <a v-if="!element.disabled" class="nav-link no-control" :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}</a>
          </li>
        </ul>

        <!-- TYPE_HEADER_MENU_DEROULANT -->
        <ul class="navbar-nav" v-if="this.type === 2">
          <li v-for="(element) in this.menu.menuElements" class="nav-item" :class="this.isHaveChildren(element) === true ? 'dropdown' : '' "
              :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
            <a v-if="!this.isHaveChildren(element) && !element.disabled" class="nav-link" :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}</a>
            <a v-else-if="!element.disabled" class="nav-link dropdown-toggle no-control" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ elementTranslate.text }}
            </a>
            <ul v-if="this.isHaveChildren(element)" class="dropdown-menu" v-html="this.renderDeepDropDown(element, '')">

            </ul>
          </li>
        </ul>

        <!-- TYPE_HEADER_MENU_DEROULANT_BIG_MENU -->
        <ul class="navbar-nav" v-if="this.type > 2">
          <li v-for="(element) in this.menu.menuElements" class="nav-item" :class="this.isHaveChildren(element) === true ? 'dropdown has-megamenu' : '' "
              :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
            <a v-if="!this.isHaveChildren(element) && !element.disabled" class="nav-link" :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}</a>
            <a v-else-if="!element.disabled" class="nav-link dropdown-toggle no-control" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ elementTranslate.text }}
            </a>
            <div v-if="this.isHaveChildren(element)" class="dropdown-menu megamenu" role="menu">
              <div class="row" v-html="this.renderBigMenu(element, '')">

              </div>
            </div>
          </li>
        </ul>

      </div>
    </div>
  </nav>


</template>
<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant footer pour la création /edition d'un menu
 */
import {MenuElementTools} from "../../../utils/Admin/Content/Menu/MenuElementsTools";

export default {
  name: "MenuFooter",
  components: {},
  emit: [],
  props: {
    menu: Object,
    type: Number,
    data: Object,
    locale: String
  },
  data() {
    return {}
  },
  mounted() {

  },
  methods: {

    /**
     * Retourne l'année courante
     * @return {number}
     */
    getCurrentYear() {
      return new Date().getFullYear();
    },

    /**
     * Retourne le nombre de colonne en fonction du type
     */
    getCurrentCol() {
      let col = 'col-3';

      switch (this.type) {
        case 13 :
          col = 'col-10'
          break
        case 14 :
          col = 'col-5'
          break
        case 15 :
          col = 'col-3'
          break
        case 16 :
          col = 'col-2'
          break
        default :
          col = 'col-3'
          break;
      }
      return col;
    },

    /**
     * Génère le footer de type Col avec profondeur
     * @param menuElement
     * @param html
     */
    renderColDeep(menuElement, html) {
      menuElement.children.forEach((element) => {
        let elementTranslate = this.getTranslationByLocale(element.menuElementTranslations);
        if (!element.disabled) {

          html += ' <li class="nav-item mb-2"><a class="nav-link p-0 text-body-secondary"  target="' + element.linkTarget + '" href="' + elementTranslate.link + '">' + elementTranslate.text + '</a>'

          if (this.isHaveChildren(element)) {

            html += '<ul>'
            html = this.renderColDeep(element, html);
            html += '</ul>';
          }
          html += '</li>';
        }
      });

      return html;
    },

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

  }
}
</script>

<template>

  <!-- Menu footer 1 ligne à droite TYPE_FOOTER_1_ROW_RIGHT !-->
  <div class="container" v-if="this.type===17">
    <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
      <p class="col-md-4 mb-0 text-body-secondary">© <span v-html="this.getCurrentYear()"></span> {{ this.data.name }}
      </p>

      <a :href="this.data.ur_site" class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <i class="bi" :class="this.data.logo"></i>&nbsp;{{ this.data.name }}
      </a>

      <ul class="nav col-md-4 justify-content-end">
        <li v-for="(element) in this.menu.menuElements" class="nav-item" :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
          <a v-if="!element.disabled" class="nav-link px-2 text-body-secondary"
              :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}
          </a>
        </li>
      </ul>
    </footer>
  </div>

  <!-- Menu footer 1 ligne centrée TYPE_FOOTER_1_ROW_CENTER !-->
  <div class="container" v-if="this.type===18">
    <footer class="py-3 my-4">
      <ul class="nav justify-content-center border-bottom pb-3 mb-3">

        <li v-for="(element) in this.menu.menuElements" class="nav-item" :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
          <a v-if="!element.disabled" class="nav-link px-2 text-body-secondary"
              :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}
          </a>
        </li>
      </ul>
      <p class="text-center text-body-secondary">© <span v-html="this.getCurrentYear()"></span> {{ this.data.name }}</p>
    </footer>
  </div>

  <div class="container" v-if="this.type < 17">
    <footer class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-5 border-top">
      <div class="col mb-2">
        <a :href="this.data.ur_site" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none">
          <i class="bi" :class="this.data.logo"></i>&nbsp;{{ this.data.name }}
        </a>
        <p class="text-body-secondary">© <span v-html="this.getCurrentYear()"></span></p>
      </div>

      <div class="col-1 mb-3"></div>

      <div v-for="(element) in this.menu.menuElements" class="mb-2" :class="this.getCurrentCol()" :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
        <div v-if="this.isHaveChildren(element) && !element.disabled">
          <h5>{{ elementTranslate.text }}</h5>
          <ul class="nav flex-column" v-html="this.renderColDeep(element, '')"></ul>
        </div>
        <div v-else-if="!element.disabled">
          <h5>
            <a :target="element.linkTarget" :href="elementTranslate.link" class="text-decoration-none">{{ elementTranslate.text }}</a>
          </h5>
        </div>
      </div>
    </footer>
  </div>

</template>
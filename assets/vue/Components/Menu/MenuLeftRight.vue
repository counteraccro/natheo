<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant left ou right pour la création /edition d'un menu
 */
import {MenuElementTools} from "../../../utils/Admin/Content/Menu/MenuElementsTools";

export default {
  name: "MenuLeftRight",
  components: {},
  emit: [],
  props: {
    menu: Object,
    type: Number,
    data: Object,
    locale: String
  },
  watch: {
    type: 'switchType'
  },
  data() {
    return {}
  },
  mounted() {
    console.log(this.data);

  },
  methods: {
    /**
     * Permet de déterminer le type de rendu
     */
    switchType() {
      switch (this.type) {
        case 11 :
          console.log('TYPE_LEFT_RIGHT_SIDE_BAR');
          break;
        case 12 :
          console.log('TYPE_LEFT_RIGHT_SIDE_BAR_ACCORDEON');
          break;
      }
    },

    definePosition() {
      if (parseInt(this.data.position) === 2) {
        return 'float-end'
      }
      return "";
    },

    /**
     * Retourne le lien avec la traduction en fonction de la locale
     * @param tabMenuElementTranslation
     */
    getTranslationByLocale(tabMenuElementTranslation) {
      console.log(tabMenuElementTranslation)
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
     * Génère les profondeurs de deepMenu
     * @param menuElement
     * @param html
     * @return string
     */
    renderCollapse(menuElement, html) {

      menuElement.children.forEach((element) => {
        let elementTranslate = this.getTranslationByLocale(element.menuElementTranslations);
        if (!element.disabled) {
          if (!this.isHaveChildren(element)) {
            html += '<li><a class="link-body-emphasis d-inline-flex text-decoration-none rounded element-hover"  target="' + element.linkTarget + '" href="' + elementTranslate.link + '">' + elementTranslate.text + '</a>'
          } else {
            html += '<li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded link-toggle element-hover no-control" data-bs-toggle="collapse" data-bs-target="#demo-' + element.id + '" aria-expanded="false"> ' + elementTranslate.text + '</a>'
            html += '<div class="collapse" id="demo-' + element.id + '">';
            html += '<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">'
            html = this.renderCollapse(element, html);
            html += '</ul></div>';
          }
          html += '</li>';
        }
      });

      return html;
    },

  }
}
</script>

<template>

  <div class="flex-shrink-0 p-3 bg-body-tertiary border border-1 border-light" :class="this.definePosition()" style="width: 280px;">
    <a :href="this.data.ur_site" class="d-flex align-items-center pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom">
      <i class="bi pe-none me-2 h4 mt-2" :class="this.data.logo"></i>
      <span class="fs-5 fw-semibold">{{ this.data.name }}</span>
    </a>

    <ul v-if="this.type === 11" class="list-unstyled ps-0">
      <li v-for="(element) in this.menu.menuElements" class="mb-1" :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
        <a v-if="!element.disabled" class="btn d-inline-flex align-items-center rounded border-0 collapsed element-hover"
            :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}
        </a>
      </li>
    </ul>

    <ul v-if="this.type === 12" class="list-unstyled ps-0">
      <li v-for="(element) in this.menu.menuElements" class="mb-1" :set="elementTranslate = this.getTranslationByLocale(element.menuElementTranslations)">
        <a v-if="!this.isHaveChildren(element) && !element.disabled" class="btn d-inline-flex align-items-center rounded border-0 collapsed element-hover"
            :target="element.linkTarget" :href="elementTranslate.link">{{ elementTranslate.text }}
        </a>
        <button v-else-if="!element.disabled" class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed" data-bs-toggle="collapse" :data-bs-target="'#demo-' + element.id" aria-expanded="false">
          {{ elementTranslate.text }}
        </button>
        <div v-if="this.isHaveChildren(element)" :id="'demo-' + element.id" class="collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small" v-html="this.renderCollapse(element, '')"></ul>
        </div>
      </li>
    </ul>
  </div>

</template>
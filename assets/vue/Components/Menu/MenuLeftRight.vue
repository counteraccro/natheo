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
    data: Object
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

  <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" :class="this.definePosition()" style="width: 280px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
      <i class="bi pe-none me-2 h5" :class="this.data.logo"></i>
      <span class="fs-4">{{ this.data.name }}</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="#" class="nav-link active" aria-current="page">
          <svg class="bi pe-none me-2" width="16" height="16">
            <use xlink:href="#home"></use>
          </svg>
          Home
        </a>
      </li>
      <li>
        <a href="#" class="nav-link link-body-emphasis">
          <svg class="bi pe-none me-2" width="16" height="16">
            <use xlink:href="#speedometer2"></use>
          </svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="#" class="nav-link link-body-emphasis">
          <svg class="bi pe-none me-2" width="16" height="16">
            <use xlink:href="#table"></use>
          </svg>
          Orders
        </a>
      </li>
      <li>
        <a href="#" class="nav-link link-body-emphasis">
          <svg class="bi pe-none me-2" width="16" height="16">
            <use xlink:href="#grid"></use>
          </svg>
          Products
        </a>
      </li>
      <li>
        <a href="#" class="nav-link link-body-emphasis">
          <svg class="bi pe-none me-2" width="16" height="16">
            <use xlink:href="#people-circle"></use>
          </svg>
          Customers
        </a>
      </li>
    </ul>
  </div>

</template>
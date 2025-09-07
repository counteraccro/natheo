<script>


import {MenuType} from "../../../../../utils/Front/Const/Menu";
import VerticalMenuElement from "./VerticalMenuElement.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'VerticalMenu',
  components: {VerticalMenuElement},
  computed: {
    MenuType() {
      return MenuType
    }
  },
  props: {
    utilsFront: Object,
    type: Number,
    slug: String,
    menu: Object,
  },
  data() {
    return {
      menuElement: Object,
    }
  },

  mounted() {
    if (this.menu.elements) {
      this.menuElement = this.menu.elements;
    } else {
      this.menuElement = this.menu;
    }
  },

  methods: {
    /**
     * Génère une url
     * @param element
     * @returns {*}
     */
    generateUrl(element) {
      return this.utilsFront.getUrl(element);
    },

    /**
     * Affichage hover
     * @param index
     * @param size
     * @returns {string}
     */
    getHoverElement(index, size) {

      if(index === 0) {
        return 'hover:rounded-t-xl'
      }

      if(index+1 === size) {
        return 'hover:rounded-b-xl'
      }

      return '';
    },

  }
}
</script>

<template>

  <nav class="w-full max-w bg-white shadow-md rounded-xl border border-neutral-200/70 sticky top-5">
    <div v-for="(element, index) in this.menuElement">
      <a v-if="!element.elements" :href="generateUrl(element)" :target="element.target"
         class="block px-5 py-3 font-semibold text-gray-800 hover:bg-theme-4-750 hover:!text-theme-1-100 transition border-b border-neutral-200/70" :class="this.getHoverElement(index, this.menuElement.length)">
        {{ element.label }}
      </a>
      <vertical-menu-element v-else
                             :utils-front="this.utilsFront"
                             :slug="this.slug"
                             :element="element"
                             :deep="0"
                             :size="this.menuElement.length"
                             :index="index"
      />
    </div>
    <span class="group/group-0 group/group-1 group/group-2 group/group-3 group/group-4 group/group-5"></span>
    <span
        class="group-open/group-0:rotate-90 group-open/group-1:rotate-90 group-open/group-2:rotate-90 group-open/group-3:rotate-90 group-open/group-4:rotate-90 group-open/group-5:rotate-90"></span>
  </nav>
</template>
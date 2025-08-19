<script>


import {MenuType} from "../../../../../utils/Front/Const/Menu";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'VerticalMenu',
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
    deep: Number
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
     * Génère les class css pour le ul
     */
    getClassUl() {
      let cssClass = 'space-y-1';
      if (this.deep === 0) {
        return cssClass + ' vertical-menu';
      }
      return cssClass + ' mt-2 pl-4';
    },

    getClassA(element) {
      let cssClass = 'block rounded-lg pr-4 py-2 font-medium pl-4 hover:bg-theme-4-750 hover:dark:bg-gray-600 hover:!text-theme-1-100';
      let select = '!text-gray-500';

      if (this.slug === element.slug) {
        select = '!text-theme-1-100 bg-theme-4-750 dark:bg-gray-600 ';
      }

      if (this.deep === 0) {
        return cssClass + ' ' + select;
      }
      return cssClass + ' hover:bg-theme-4-750 hover:!text-theme-1-100  ' + select;
    },
  }
}
</script>

<template>

  <ul :class="this.getClassUl()" :style="'margin-left: ' + this.deep + ' em'">
    <li v-for="element in this.menuElement">
      <a v-if="!element.elements || this.type === MenuType.leftRightSideBar" :class="this.getClassA(element)"
         :href="generateUrl(element)"
         :target="element.target">{{ element.label }}</a>

      <details v-else :tabindex="this.deep" class="group [&_summary::-webkit-details-marker]:hidden">
        <summary
            class="flex cursor-pointer items-center justify-between rounded-lg p-4 py-2 !text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 hover:dark:bg-gray-600">
          <span class="font-medium"> {{ element.label }} </span>
          <span class="shrink-0 transition duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"/>
          </svg>
        </span>
        </summary>

        <div>
          <VerticalMenu
              :utils-front="this.utilsFront"
              :type="this.type"
              :slug="this.slug"
              :menu="element.elements"
              :deep="this.deep + 1"
          />
        </div>
      </details>
    </li>
  </ul>
</template>
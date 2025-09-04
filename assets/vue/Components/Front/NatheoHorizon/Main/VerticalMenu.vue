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

  <nav class="w-full max-w-sm bg-white shadow-md rounded-xl border border-gray-200 mb-5">

    <a href="#"
       class="block px-5 py-3 font-semibold text-gray-800 hover:bg-blue-50 hover:text-blue-700 transition border-b">
      À propos
    </a>

    <details class="group/parent border-b">
      <summary
          class="flex items-center justify-between cursor-pointer px-5 py-3 font-semibold text-gray-800 hover:bg-gray-50">
        Produits
        <span class="transition-transform group-open/parent:rotate-90 text-gray-500">➤</span>
      </summary>
      <div class="bg-gray-50">
        <details class="group/sub border-t">
          <summary
              class="flex items-center justify-between cursor-pointer px-6 py-2 text-gray-700 hover:bg-gray-100 font-medium">
            Catégorie A
            <span class="transition-transform group-open/sub:rotate-90 text-gray-500">➤</span>
          </summary>
          <ul class="space-y-1 pb-2">
            <li>
              <a href="#"
                 class="block px-8 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
                Produit A1
              </a>
            </li>
            <li>
              <a href="#"
                 class="block px-8 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
                Produit A2
              </a>
            </li>
          </ul>
        </details>

        <ul class="space-y-1 border-t">
          <li>
            <a href="#"
               class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
              Catégorie B
            </a>
          </li>
          <li>
            <a href="#"
               class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
              Catégorie C
            </a>
          </li>
        </ul>
      </div>
    </details>

    <details class="group/parent border-b">
      <summary
          class="flex items-center justify-between cursor-pointer px-5 py-3 font-semibold text-gray-800 hover:bg-gray-50">
        Services
        <span class="transition-transform group-open/parent:rotate-90 text-gray-500">➤</span>
      </summary>
      <ul class="bg-gray-50 space-y-1 pb-2 border-t">
        <li>
          <a href="#" class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
            Conseil
          </a>
        </li>
        <li>
          <a href="#"
             class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition active">
            Support
          </a>
        </li>
      </ul>
    </details>

    <details class="group/parent">
      <summary
          class="flex items-center justify-between cursor-pointer px-5 py-3 font-semibold text-gray-800 hover:bg-gray-50">
        Contact
        <span class="transition-transform group-open/parent:rotate-90 text-gray-500">➤</span>
      </summary>
      <ul class="bg-gray-50 space-y-1 pb-2 border-t">
        <li>
          <a href="#" class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
            Email
          </a>
        </li>
        <li>
          <a href="#" class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
            Téléphone
          </a>
        </li>
      </ul>
    </details>
  </nav>

  <nav class="w-full max-w bg-white shadow-md rounded-xl border border-gray-200">
    <div v-for="(element, index) in this.menuElement">
      <a v-if="!element.elements" :href="generateUrl(element)" :target="element.target"
         class="block px-5 py-3 font-semibold text-gray-800 hover:bg-blue-50 hover:text-blue-700 transition border-b">
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

  <!-- Sidebar -->

  <!--<ul :class="this.getClassUl()" :style="'margin-left: ' + this.deep + ' em'">
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
  </ul> -->
</template>
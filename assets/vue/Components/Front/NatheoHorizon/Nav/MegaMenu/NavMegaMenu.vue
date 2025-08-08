<script>
import NavMegaMenuSub from "./NavMegaMenuSub.vue";
import {MenuType} from "../../../../../../utils/Front/Const/Menu";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'NavMegaMenu',
  components: {NavMegaMenuSub},
  props: {
    data: Object,
    utilsFront: Object,
    type: Number,
  },
  emits: [],
  data() {
    return {
      elementsChild: [],
      elements: []
    }
  },
  mounted() {
    this.constructArray(this.data);
  },

  methods: {

    /**
     * Génères les tableaux pour le render
     * @param data
     */
    constructArray(data) {
      for (let key in data.elements) {
        if (data.elements[key].hasOwnProperty('elements')) {
          this.elementsChild.push(data.elements[key]);
        } else
          this.elements.push(data.elements[key]);
      }
    },

    /**
     * Retourne le nombre de colonnes à générer
     * @param data
     * @returns {number}
     */
    getNbCol(data) {

      if (this.type === MenuType.headerBigMenu2column) {
        return 2
      }

      if (this.type === MenuType.headerBigMenu3column) {
        return 3
      }

      if (this.type === MenuType.headerBigMenu4column) {
        return 4
      }

      let nb = 0;
      let noChildren = 0;
      for (let key in data.elements) {
        if (data.elements[key].hasOwnProperty('elements')) {
          nb++;
        } else if (noChildren < 1)
          noChildren++;
      }
      return (nb + noChildren);
    }
  }
}
</script>

<template>
  <div class="relative group inline-block">
    <!-- Bouton principal -->
    <a href="#"
       class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-4 py-2 rounded-md text-sm font-medium inline-flex items-center gap-1 hover:dark:bg-gray-600">
      {{ this.data.label }}
      <svg xmlns="http://www.w3.org/2000/svg" class="size-5 inline-block" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd"/>
      </svg>
    </a>

    <!-- Mega menu panel -->
    <div class="absolute left-0 hidden group-hover:block group-focus-within:block bg-white border border-gray-200 rounded-lg shadow-lg z-20 w-[700px]
              opacity-0 translate-y-3 transition-all duration-300 ease-out group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:translate-y-0">
      <div class="grid gap-6 p-6" :class="'grid-cols-' + this.getNbCol(this.data)">
        <!-- Colonne 1 -->

        <div v-for="element in this.elementsChild">
          <h3 v-if="element.hasOwnProperty('elements')" class="text-sm font-semibold text-gray-700 mb-2">
            {{ element.label }}</h3>
          <nav-mega-menu-sub
              :data="element.elements"
              :utils-front="this.utilsFront"
              :deep="0"
          />
        </div>

        <div v-if="elements.length > 0">
          <ul>
            <li v-for="element in this.elements">
              <a
                  :href="this.utilsFront.getUrl(element)"
                  class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium hover:dark:bg-gray-600"
                  :target="element.target">
                {{ element.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="grid-cols-1 grid-cols-2 grid-cols-3 grid-cols-4 grid-cols-5 grid-cols-6 grid-cols-7 grid-cols-8">
    <!-- generate tailwindClass -->
  </div>

</template>
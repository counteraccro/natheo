<script>
import VerticalMenu from "../../Main/VerticalMenu.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'NavMenuDropdownMobile',
  components: {VerticalMenu},
  props: {
    data: Object,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {}
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>
    <li v-for="element in this.data.elements">
      <a :href="this.utilsFront.getUrl(element)" v-if="!element.hasOwnProperty('elements')"
         class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 p block px-3 py-2 rounded-md text-base font-medium"
         :target="element.target">

        <svg class="w-5 h-5 inline-block text-center" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
        </svg>


        {{ element.label }}</a>
      <a v-else href="#" onclick="return false"
         class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 p px-3 py-2 rounded-md text-base font-medium w-full flex items-center justify-between mobile-dropdown-trigger">
        {{ element.label }}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"/>
        </svg>
      </a>
      <ul v-if="element.hasOwnProperty('elements')" class="hidden ps-4 py-2 mobile-dropdown-content">
        <nav-menu-dropdown-mobile
            :data="element"
            :utilsFront="this.utilsFront"
        />
      </ul>
    </li>
</template>
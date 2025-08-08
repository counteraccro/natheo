<script>
import NavMenuDropdownSub from "./NavMenuDropdownSub.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Dropdown
 */
export default {
  name: 'NavMenuDropdown',
  components: {NavMenuDropdownSub},
  props: {
    data: Object,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>
  <div class="relative group inline-block">
    <!-- lien principal -->
    <a href="#"
       class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-4 py-2 rounded-md text-sm font-medium hover:dark:bg-gray-600">
      {{ this.data.label }}
      <svg xmlns="http://www.w3.org/2000/svg" class="size-5 inline-block" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd"/>
      </svg>
    </a>

    <!-- Menu principal -->
    <div class="absolute z-10 hidden group-hover:block group-focus-within:block min-w-max bg-white border border-gray-200 rounded-lg shadow-lg mt-1
              opacity-0 translate-y-2 transition-all duration-200 ease-out group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:translate-y-0">
      <ul class="py-2 px-4">

        <li v-for="element in this.data.elements" class="relative group/level-1 focus-within:bg-theme-4-750 focus-within:rounded-md focus-within:text-shadow-theme-1-100">
          <a :href="this.utilsFront.getUrl(element)" v-if="!element.hasOwnProperty('elements')"
             class="block px-4 py-2 hover:bg-theme-4-750 !text-gray-500 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600">{{ element.label }}</a>
          <nav-menu-dropdown-sub v-else
                                 :data="element"
                                 :utils-front="this.utilsFront"/>
        </li>
      </ul>
    </div>
  </div>
</template>
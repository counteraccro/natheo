<script>
import NavMegaMenu from "./MegaMenu/NavMegaMenu.vue";
import {MenuType} from "../../../../../utils/Front/Const/Menu";
import NavMenuDropdown from "./Dropdown/NavMenuDropdown.vue";
import NavMenuDropdownMobile from "./Dropdown/NavMenuDropdownMobile.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Nav',
  computed: {
    MenuType() {
      return MenuType
    }
  },
  components: {NavMenuDropdownMobile, NavMenuDropdown, NavMegaMenu},
  props: {
    optionsSystem: Object,
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
    this.utilsFront.eventDropDownMobileToggle();
  },

  methods: {}
}
</script>

<template>
  <nav>
    <div>
      <div class="relative flex items-center justify-between h-16">
        <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
          <div class="flex-shrink-0 flex items-center">
            <img class="block h-16 w-auto" src="/assets/natheo/front/logo_transparent.png" alt="Logo">
            <span class="ml-2 text-xl font-bold text-gray-800">{{ this.optionsSystem.OS_SITE_NAME }}</span>
          </div>
          <div class="hidden lg:block sm:ml-6">
            <div class="flex space-x-4 items-center h-16">


                <!-- menu type sidebar -->
                <div v-for="element in this.data.elements" v-if="this.data.type === this.MenuType.headerSideBar">
                  <a
                      :href="this.utilsFront.getUrl(element)"
                      class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium"
                      :target="element.target">
                    {{ element.label }}
                  </a>
                </div>

                <!-- menu type dropdown -->
                <div v-for="element in this.data.elements" v-if="this.data.type === this.MenuType.headerDropDown">
                <a v-if="!element.hasOwnProperty('elements')" :href="this.utilsFront.getUrl(element)"
                   class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium"
                   :target="element.target">{{ element.label }}</a>
                <NavMenuDropdown v-else
                                 :utils-front="this.utilsFront"
                                 :data="element"
                />
              </div>

                <div v-for="element in this.data.elements" v-if="this.data.type === this.MenuType.headerBigMenu
              || this.data.type === this.MenuType.headerBigMenu2column || this.data.type === this.MenuType.headerBigMenu3column ||
              this.data.type === this.MenuType.headerBigMenu4column">
                <a v-if="!element.hasOwnProperty('elements')" :href="this.utilsFront.getUrl(element)"
                   class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium"
                   :target="element.target">{{ element.label }}</a>

                <NavMegaMenu v-else
                             :utilsFront="this.utilsFront"
                             :data="element"
                             :type="this.data.type"
                />
              </div>
            </div>
          </div>
        </div>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 lg:static lg:inset-auto lg:ml-6 lg:pr-0">
          <div class="hidden lg:flex lg:items-center">
            <a href="#"
               class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium">Login</a>
          </div>

          <!-- Mobile menu button -->
          <div class="lg:hidden">
            <button type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-theme-4-750"
                    aria-expanded="false" id="mobile-menu-button">
              <span class="sr-only">Open main menu</span>
              <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>


    <!-- Mobile menu, show/hide based on menu state -->
    <div class="lg:hidden hidden" id="mobile-menu">
      <div class="px-2 pt-2 pb-3 space-y-1">

        <a v-for="element in this.data.elements" v-if="this.data.type === this.MenuType.headerSideBar"
           :href="this.utilsFront.getUrl(element)"
           class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 p block px-3 py-2 rounded-md text-base font-medium"
           :target="element.target">{{ element.label }}</a>

        <ul v-if="this.data.type === this.MenuType.headerDropDown">
          <nav-menu-dropdown-mobile
              :data="this.data"
              :utilsFront="this.utilsFront"
          />
        </ul>
        <!--<a href="#" class="bg-gray-100 text-gray-900 block px-3 py-2 rounded-md text-base font-medium">Home</a>-->

        <!-- Mobile Products Dropdown -->
        <!--<div class="relative">
          <button
              class="w-full text-left text-gray-900 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium flex items-center justify-between mobile-dropdown-trigger">
            Products
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"/>
            </svg>
          </button>
          <div class="hidden px-4 py-2 mobile-dropdown-content">
            <div class="border-l-2 border-indigo-500 pl-2 mb-4">
              <h4 class="font-medium text-gray-900 mb-2">Software</h4>
              <ul class="space-y-2">
                <li><a href="#" class="text-gray-600 hover:text-indigo-600 block">Web Development</a></li>
                <li><a href="#" class="text-gray-600 hover:text-indigo-600 block">Mobile Apps</a></li>
                <li><a href="#" class="text-gray-600 hover:text-indigo-600 block">Desktop Software</a></li>
              </ul>
            </div>
            <div class="border-l-2 border-indigo-500 pl-2">
              <h4 class="font-medium text-gray-900 mb-2">Hardware</h4>
              <ul class="space-y-2">
                <li><a href="#" class="text-gray-600 hover:text-indigo-600 block">Laptops</a></li>
                <li><a href="#" class="text-gray-600 hover:text-indigo-600 block">Desktops</a></li>
                <li><a href="#" class="text-gray-600 hover:text-indigo-600 block">Accessories</a></li>
              </ul>
            </div>
          </div>
        </div>

        <a href="#"
           class="text-gray-900 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Services</a>
        <a href="#" class="text-gray-900 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">About</a>
        <a href="#" class="text-gray-900 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Contact</a> -->

        <div class="pt-4 pb-3 border-t border-gray-200">
          <div class="flex items-center px-3 space-y-2 flex-col">
            <a href="#"
               class="block w-full text-center !text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-base font-medium">
              Login
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
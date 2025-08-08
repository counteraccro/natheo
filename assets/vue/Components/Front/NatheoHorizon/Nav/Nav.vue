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
    userInfo: Object,
    utilsFront: Object,
    translate: Object,
    urls: Object
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
            <span class="ml-2 text-xl font-bold text-gray-800">{{
                this.optionsSystem.OS_SITE_NAME
              }}</span>
          </div>
          <div class="hidden lg:block sm:ml-6">
            <div class="flex space-x-4 items-center h-16">


              <!-- menu type sidebar -->
              <div v-for="element in this.data.elements" v-if="this.data.type === this.MenuType.headerSideBar">
                <a
                    :href="this.utilsFront.getUrl(element)"
                    class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium hover:dark:bg-gray-600"
                    :target="element.target">
                  {{ element.label }}
                </a>
              </div>

              <!-- menu type dropdown -->
              <div v-for="element in this.data.elements" v-if="this.data.type === this.MenuType.headerDropDown">
                <a v-if="!element.hasOwnProperty('elements')" :href="this.utilsFront.getUrl(element)"
                   class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium hover:dark:bg-gray-600"
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
                   class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium hover:dark:bg-gray-600"
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

            <button onclick="(() => document.documentElement.classList.toggle('dark'))()"
                    class="group h-10 w-10 rounded-full p-2 hover:bg-theme-4-750 dark:hover:bg-gray-600 me-2 cursor-pointer">
              <svg class="fill-violet-700 block group-hover:fill-theme-1-100 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
              </svg>
              <svg class="fill-yellow-500 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                    fill-rule="evenodd" clip-rule="evenodd"></path>
              </svg>
            </button>

            <a :href="this.urls.adminAuth" v-if="this.userInfo === ''"
               class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-sm font-medium">
              {{ this.translate.login }}
            </a>

            <button v-else class="flex items-center space-x-2 focus:outline-none">
              <div class="relative">
                <div
                    class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-theme-4-750 rounded-full dark:bg-gray-600">
                  <span class="font-medium text-theme-1-100 dark:text-gray-300">{{ this.userInfo.avatar }}</span>
                </div>
              </div>
              <div class="hidden lg:flex flex-col items-start">
                <span class="text-sm font-medium text-gray-500">{{ this.userInfo.login }}</span>
                <a :href="this.urls.logout" class="text-xs text-gray-500">{{ this.translate.logout }}</a>
              </div>
              <i class="fas fa-chevron-down text-xs text-gray-500 hidden lg:inline transition-transform duration-200 group-hover:text-blue-600"></i>
            </button>
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
           class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 p block px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600"
           :target="element.target">{{ element.label }}</a>

        <ul v-if="this.data.type !== this.MenuType.headerSideBar">
          <nav-menu-dropdown-mobile
              :data="this.data"
              :utilsFront="this.utilsFront"
          />
        </ul>
        <div class="pt-4 pb-3 border-t border-gray-200">
          <div class="flex items-center px-3 space-y-2 flex-col">

            <div class="group block w-full text-center hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md text-base cursor-pointer hover:dark:bg-gray-600"
                 onclick="(() => document.documentElement.classList.toggle('dark'))()">
              <div
                  class="h-10 w-10 rounded-lg p-2 m-auto">
                <svg class="fill-violet-700 group-hover:fill-theme-1-100 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg class="fill-yellow-500 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                  <path
                      d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                      fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>

            <a :href="this.urls.adminAuth" v-if="this.userInfo === ''"
               class="block w-full text-center !text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600">
              {{ this.translate.login }}
            </a>
            <a :href="this.urls.logout" v-else
               class="block w-full text-center !text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600">
              [{{ this.userInfo.login }}] {{ this.translate.logout }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
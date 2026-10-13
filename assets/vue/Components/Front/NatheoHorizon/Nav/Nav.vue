<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Menu principal (header) du front
 */
import { defineComponent, type PropType } from 'vue';
import NavMegaMenu from './MegaMenu/NavMegaMenu.vue';
import NavMenuDropdown from './Dropdown/NavMenuDropdown.vue';
import NavMenuDropdownMobile from './Dropdown/NavMenuDropdownMobile.vue';
import { MenuType } from '@/utils/Front/Const/Menu';
import { UtilsFront } from '@/utils/Front/UtilsFront';
import type {
  FrontHeaderTranslate,
  FrontMenu,
  FrontUrls,
  FrontUserInfo,
} from '@/ts/Front/NatheoHorizon/Structure.type';

const BIG_MENU_TYPES: number[] = [
  MenuType.headerBigMenu,
  MenuType.headerBigMenu2column,
  MenuType.headerBigMenu3column,
  MenuType.headerBigMenu4column,
];

export default defineComponent({
  name: 'Nav',
  components: { NavMenuDropdownMobile, NavMenuDropdown, NavMegaMenu },
  props: {
    data: {
      type: Object as PropType<FrontMenu>,
      required: true,
    },
    userInfo: {
      type: Object as PropType<FrontUserInfo | null>,
      default: null,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontHeaderTranslate>,
      required: true,
    },
    urls: {
      type: Object as PropType<FrontUrls>,
      required: true,
    },
  },
  data() {
    return {
      mobileOpen: false,
    };
  },
  computed: {
    isSideBar(): boolean {
      return this.data.type === MenuType.headerSideBar;
    },
    isDropDown(): boolean {
      return this.data.type === MenuType.headerDropDown;
    },
    isBigMenu(): boolean {
      return BIG_MENU_TYPES.includes(this.data.type);
    },
    siteName(): string {
      return this.utilsFront.optionsSystem.OS_SITE_NAME;
    },
    logoSrc(): string {
      return this.utilsFront.datas.basePath + '/assets/natheo/front/logo_transparent.png';
    },
    avatarSrc(): string | null {
      if (!this.userInfo?.avatarImg) {
        return null;
      }
      return this.utilsFront.datas.pathAvatar + this.userInfo.avatarImg;
    },
  },
  methods: {
    toggleDarkMode(): void {
      UtilsFront.toggleDarkMode();
    },
  },
});
</script>

<template>
  <nav>
    <div class="relative flex items-center justify-between h-16">
      <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
        <div class="shrink-0 flex items-center">
          <img class="block h-16 w-auto" :src="logoSrc" alt="" />
          <span class="ml-2 text-xl font-bold text-gray-800">{{ siteName }}</span>
        </div>
        <div class="hidden lg:block sm:ml-6">
          <div class="flex space-x-4 items-center h-16">
            <div v-for="element in data.elements" :key="element.id">
              <a
                v-if="isSideBar || !element.elements"
                :href="utilsFront.getUrl(element)"
                class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-3 py-2 rounded-md font-medium hover:dark:bg-gray-600"
                :class="{ 'text-sm': !isBigMenu }"
                :target="element.target"
              >
                {{ element.label }}
              </a>
              <NavMenuDropdown v-else-if="isDropDown" :utils-front="utilsFront" :data="element" />
              <NavMegaMenu v-else-if="isBigMenu" :utils-front="utilsFront" :data="element" :type="data.type" />
            </div>
          </div>
        </div>
      </div>
      <div class="absolute inset-y-0 right-0 flex items-center pr-2 lg:static lg:inset-auto lg:ml-6 lg:pr-0">
        <div class="hidden lg:flex lg:items-center">
          <button
            type="button"
            class="group h-10 w-10 rounded-full p-2 hover:bg-theme-4-750 dark:hover:bg-gray-600 me-2 cursor-pointer border border-gray-300"
            :aria-label="translate.darkMode"
            :title="translate.darkMode"
            @click="toggleDarkMode"
          >
            <svg
              class="fill-violet-700 block group-hover:fill-theme-1-100 dark:hidden"
              viewBox="0 0 20 20"
              aria-hidden="true"
            >
              <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
            </svg>
            <svg class="fill-yellow-500 hidden dark:block" viewBox="0 0 20 20" aria-hidden="true">
              <path
                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                fill-rule="evenodd"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>

          <a
            v-if="!userInfo"
            :href="urls.adminAuth"
            class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-3 py-2 rounded-md font-medium"
          >
            {{ translate.login }}
          </a>

          <div v-else class="flex items-center space-x-2">
            <div class="relative">
              <div
                v-if="!avatarSrc"
                class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-theme-4-750 rounded-full dark:bg-gray-600"
              >
                <span class="font-medium text-theme-1-100 dark:text-gray-300">{{ userInfo.avatar }}</span>
              </div>
              <div
                v-else
                class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden rounded-full mt-1"
              >
                <img :src="avatarSrc" :alt="userInfo.login" class="w-10 h-10" />
              </div>
            </div>
            <div class="hidden lg:flex flex-col items-start">
              <span class="font-medium text-slate-600">{{ userInfo.login }}</span>
              <a :href="urls.logout" class="text-xs text-slate-600">{{ translate.logout }}</a>
            </div>
          </div>
        </div>

        <!-- Bouton menu mobile -->
        <div class="lg:hidden">
          <button
            type="button"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-theme-4-750"
            :aria-expanded="mobileOpen"
            aria-controls="mobile-menu"
            @click="mobileOpen = !mobileOpen"
          >
            <span class="sr-only">{{ translate.openMenu }}</span>
            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Menu mobile -->
    <div v-show="mobileOpen" id="mobile-menu" class="lg:hidden">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <template v-if="isSideBar">
          <a
            v-for="element in data.elements"
            :key="element.id"
            :href="utilsFront.getUrl(element)"
            class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! block px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600"
            :target="element.target"
            >{{ element.label }}</a
          >
        </template>
        <ul v-else>
          <NavMenuDropdownMobile :elements="data.elements" :utils-front="utilsFront" />
        </ul>
        <div class="pt-4 pb-3 border-t border-gray-200">
          <div class="flex items-center px-3 space-y-2 flex-col">
            <button
              type="button"
              class="group block w-full text-center hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md text-base cursor-pointer hover:dark:bg-gray-600"
              :aria-label="translate.darkMode"
              @click="toggleDarkMode"
            >
              <span class="block h-10 w-10 rounded-lg p-2 m-auto">
                <svg
                  class="fill-violet-700 group-hover:fill-theme-1-100 block dark:hidden"
                  viewBox="0 0 20 20"
                  aria-hidden="true"
                >
                  <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg class="fill-yellow-500 hidden dark:block" viewBox="0 0 20 20" aria-hidden="true">
                  <path
                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                  ></path>
                </svg>
              </span>
            </button>

            <a
              v-if="!userInfo"
              :href="urls.adminAuth"
              class="block w-full text-center text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600"
            >
              {{ translate.login }}
            </a>
            <a
              v-else
              :href="urls.logout"
              class="block w-full text-center text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600"
            >
              [{{ userInfo.login }}] {{ translate.logout }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

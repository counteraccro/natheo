<script>
import FooterColonne from "./FooterColonne.vue";
import {MenuType} from "../../../../../utils/Front/Const/Menu";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Footer',
  components: {FooterColonne},
  props: {
    optionsSystem: Object,
    translate: Object,
    urls: Object,
    data: Object,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {
      menuType: {
        colonne: MenuType.footer4Column,
        left: MenuType.footer1LigneRight,
        center: MenuType.footer1LigneCenter
      },
      css: {
        16: {
          flexDirection: 'justify-between',
          w: 'max-w-md'
        },
        17: {
          flexDirection: 'justify-end',
          w: 'w-7/10'
        },
        18: {
          flexDirection: 'justify-center',
          w: 'max-w'
        },
      }
    }
  },
  mounted() {

  },

  methods: {
    /**
     * Génère une url
     * @param element
     * @returns {*}
     */
    generateUrl(element) {
      return this.utilsFront.getUrl(element)
    }
  }
}
</script>

<template>

  <div class="flex flex-wrap gap-10" :class="this.css[this.data.type].flexDirection">
    <div :class="this.css[this.data.type].w">
      <a :href="this.optionsSystem.OS_ADRESSE_SITE"
         class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">
        {{ this.optionsSystem.OS_SITE_NAME }}
      </a>
      <div v-if="this.data.type === this.menuType.colonne" class="mt-6">
        <p class="text-slate-600 leading-relaxed ">
          {{ this.optionsSystem.OS_FRONT_FOOTER_TEXTE }}
        </p>
      </div>
      <ul v-if="this.data.type === this.menuType.colonne" class="mt-5 flex space-x-5">
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_X_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_X_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/x_logo_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/facebook_logo_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_GITHUB_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_GITHUB_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/github_logo_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/instagram_logo_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/linkedin_logo_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/tiktok_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
        <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL !== ''">
          <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/youtube_logo_icon.png" class="w-10 h-auto"/>
          </a>
        </li>
      </ul>
    </div>

    <div v-if="this.data.type === this.menuType.colonne" v-for="element in this.data.elements"
         class="max-lg:min-w-[140px]">
      <h5 class="text-slate-900 font-semibold  relative max-sm:cursor-pointer">{{ element.label }}</h5>
      <FooterColonne v-if="element.hasOwnProperty('elements')"
                     :elements="element.elements"
                     :deep="0"
                     :utils-front="this.utilsFront"
      />
    </div>
    <div v-if="this.data.type === this.menuType.left" v-for="element in this.data.elements"
         class="max-lg:min-w-[140px]">
      <a :href="this.generateUrl(element)" :target="element.target"
         class="hover:text-slate-900 text-slate-600  font-normal">
        {{ element.label }}
      </a>
    </div>

    <div v-if="this.data.type === this.menuType.center" v-for="element in this.data.elements"
         class="max-lg:min-w-[140px]">
      <a :href="this.generateUrl(element)" :target="element.target"
         class="hover:text-slate-900 text-slate-600  font-normal">
        {{ element.label }}
      </a>
    </div>
  </div>

  <div>
    <ul class="md:flex md:space-x-6 max-md:space-y-2 mt-3">
      <li>
        <a :href="this.urls.indexFr"
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            this.translate.frLink
          }}</a>
      </li>
      <li>
        <a :href="this.urls.indexEn"
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            this.translate.enLink
          }}</a>
      </li>
      <li>
        <a :href="this.urls.indexEs"
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            this.translate.esLink
          }}</a>
      </li>
    </ul>
  </div>

  <hr class="mt-5 mb-5 border-gray-300"/>

  <div class="flex flex-wrap max-md:flex-col gap-4">
    <ul class="md:flex md:space-x-6 max-md:space-y-2">
      <li>
        <a href='https://github.com/counteraccro/natheo'
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            this.translate.githubLink
          }}</a>
      </li>
      <li>
        <a :href="this.urls.adminAuth"
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            this.translate.adminLink
          }}</a>
      </li>
      <li>
        <a :href="this.urls.sitemap"
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            this.translate.sitemapLink
          }}</a>
      </li>
      <li>
        <a href='#'
           class="text-slate-600  font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">{{
            translate.templateVersion
          }}</a>
      </li>
    </ul>

    <p class="text-slate-600  md:ml-auto">{{ this.translate.credit }}</p>
  </div>
</template>
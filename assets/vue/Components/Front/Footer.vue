<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Footer',
  props: {
    optionsSystem : Object,
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
    console.log(this.optionsSystem)
  },

  methods: {

  }
}
</script>

<template>

    <div class="flex flex-wrap justify-between gap-10">
      <div class="max-w-md">
        <a :href="this.optionsSystem.OS_ADRESSE_SITE">
          {{ this.optionsSystem.OS_SITE_NAME }}
        </a>
        <div class="mt-6">
          <p class="text-slate-600 leading-relaxed text-sm">
            {{ this.optionsSystem.OS_FRONT_FOOTER_TEXTE }}
          </p>
        </div>
        <ul class="mt-10 flex space-x-5">
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_X_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_X_URL">
              <img src="/assets/natheo/front/reseaux-sociaux/x_logo_icon.png" class="w-10 h-auto" />
            </a>
          </li>
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL">
              <img src="/assets/natheo/front/reseaux-sociaux/facebook_logo_icon.png" class="w-10 h-auto" />
            </a>
          </li>
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_GITHUB_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_GITHUB_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/github_logo_icon.png" class="w-10 h-auto" />
            </a>
          </li>
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/instagram_logo_icon.png" class="w-10 h-auto" />
            </a>
          </li>
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/linkedin_logo_icon.png" class="w-10 h-auto" />
            </a>
          </li>
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/tiktok_icon.png" class="w-10 h-auto" />
            </a>
          </li>
          <li v-if="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL !== ''">
            <a :href="this.optionsSystem.OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL">
            <img src="/assets/natheo/front/reseaux-sociaux/youtube_logo_icon.png" class="w-10 h-auto" />
            </a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Services</h4>

        <ul class="mt-6 space-y-4">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Web Development</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Pricing</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Support</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Client Portal</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Resources</a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Platforms</h4>
        <ul class="space-y-4 mt-6">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Hubspot</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Integration Services</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Marketing Glossar</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">UIPath</a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Company</h4>

        <ul class="space-y-4 mt-6">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">About us</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Careers</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Blog</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Portfolio</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Events</a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Additional</h4>

        <ul class="space-y-4 mt-6">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">FAQ</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Partners</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Sitemap</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Contact</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">News</a>
          </li>
        </ul>
      </div>
    </div>

    <hr class="mt-10 mb-6 border-gray-300" />

    <div class="flex flex-wrap max-md:flex-col gap-4">
      <ul class="md:flex md:space-x-6 max-md:space-y-2">
        <li>
          <a href='https://github.com/counteraccro/natheo' class="hover:text-slate-900 text-slate-600 text-sm font-normal">{{ this.translate.githubLink }}</a>
        </li>
        <li>
          <a :href="this.urls.adminAuth" class="hover:text-slate-900 text-slate-600 text-sm font-normal">{{ this.translate.adminLink }}</a>
        </li>
        <li>
          <a href='#' class="hover:text-slate-900 text-slate-600 text-sm font-normal">{{ translate.templateVersion }}</a>
        </li>
      </ul>

      <p class="text-slate-600 text-sm md:ml-auto">{{ this.translate.credit }}</p>
    </div>
</template>
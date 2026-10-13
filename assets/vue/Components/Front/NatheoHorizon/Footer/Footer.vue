<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Footer du front
 */
import { defineComponent, type PropType } from 'vue';
import FooterColonne from './FooterColonne.vue';
import { MenuType } from '@/utils/Front/Const/Menu';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontFooterTranslate, FrontMenu, FrontUrls } from '@/ts/Front/NatheoHorizon/Structure.type';

interface FooterLayout {
  justify: string;
  width: string;
}

interface SocialLink {
  name: string;
  url: string;
  icon: string;
}

const LAYOUTS: Record<number, FooterLayout> = {
  [MenuType.footer4Column]: { justify: 'justify-between', width: 'max-w-md' },
  [MenuType.footer1LigneRight]: { justify: 'justify-end', width: 'w-7/10' },
  [MenuType.footer1LigneCenter]: { justify: 'justify-center', width: 'max-w-full' },
};

export default defineComponent({
  name: 'Footer',
  components: { FooterColonne },
  props: {
    translate: {
      type: Object as PropType<FrontFooterTranslate>,
      required: true,
    },
    urls: {
      type: Object as PropType<FrontUrls>,
      required: true,
    },
    data: {
      type: Object as PropType<FrontMenu>,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
  },
  computed: {
    isColumn(): boolean {
      return this.data.type === MenuType.footer4Column;
    },

    layout(): FooterLayout {
      return LAYOUTS[this.data.type] ?? LAYOUTS[MenuType.footer1LigneCenter];
    },

    socialLinks(): SocialLink[] {
      const options = this.utilsFront.optionsSystem;
      const path = this.utilsFront.datas.basePath + '/assets/natheo/front/reseaux-sociaux/';
      return [
        { name: 'X', url: options.OS_FRONT_FOOTER_SOCIAL_X_URL, icon: path + 'x_logo_icon.png' },
        { name: 'Facebook', url: options.OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL, icon: path + 'facebook_logo_icon.png' },
        { name: 'GitHub', url: options.OS_FRONT_FOOTER_SOCIAL_GITHUB_URL, icon: path + 'github_logo_icon.png' },
        {
          name: 'Instagram',
          url: options.OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL,
          icon: path + 'instagram_logo_icon.png',
        },
        { name: 'LinkedIn', url: options.OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL, icon: path + 'linkedin_logo_icon.png' },
        { name: 'TikTok', url: options.OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL, icon: path + 'tiktok_icon.png' },
        { name: 'YouTube', url: options.OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL, icon: path + 'youtube_logo_icon.png' },
      ].filter((link) => !!link.url);
    },
  },
});
</script>

<template>
  <div class="flex flex-wrap gap-10" :class="layout.justify">
    <div :class="layout.width">
      <a
        :href="utilsFront.optionsSystem.OS_ADRESSE_SITE"
        class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
      >
        {{ utilsFront.optionsSystem.OS_SITE_NAME }}
      </a>
      <template v-if="isColumn">
        <div class="mt-6">
          <p class="text-slate-600 leading-relaxed">
            {{ utilsFront.optionsSystem.OS_FRONT_FOOTER_TEXTE }}
          </p>
        </div>
        <ul class="mt-5 flex space-x-5">
          <li v-for="social in socialLinks" :key="social.name">
            <a :href="social.url" :aria-label="social.name" target="_blank" rel="noopener">
              <img :src="social.icon" :alt="social.name" class="w-10 h-auto" />
            </a>
          </li>
        </ul>
      </template>
    </div>

    <template v-if="isColumn">
      <div v-for="element in data.elements" :key="element.id" class="max-lg:min-w-[140px]">
        <h5 class="text-slate-900 font-semibold relative">{{ element.label }}</h5>
        <FooterColonne v-if="element.elements" :elements="element.elements" :deep="0" :utils-front="utilsFront" />
      </div>
    </template>
    <template v-else>
      <div v-for="element in data.elements" :key="element.id" class="max-lg:min-w-[140px]">
        <a
          :href="utilsFront.getUrl(element)"
          :target="element.target"
          class="hover:text-slate-900 text-slate-600 font-normal"
        >
          {{ element.label }}
        </a>
      </div>
    </template>
  </div>

  <div>
    <ul class="md:flex md:space-x-6 max-md:space-y-2 mt-3">
      <li>
        <a
          :href="urls.indexFr"
          hreflang="fr"
          class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.frLink }}</a
        >
      </li>
      <li>
        <a
          :href="urls.indexEn"
          hreflang="en"
          class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.enLink }}</a
        >
      </li>
      <li>
        <a
          :href="urls.indexEs"
          hreflang="es"
          class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.esLink }}</a
        >
      </li>
    </ul>
  </div>

  <hr class="mt-5 mb-5 border-gray-300" />

  <div class="flex flex-wrap max-md:flex-col gap-4">
    <ul class="md:flex md:space-x-6 max-md:space-y-2">
      <li>
        <a
          href="https://github.com/counteraccro/natheo"
          target="_blank"
          rel="noopener"
          class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.githubLink }}</a
        >
      </li>
      <li>
        <a
          :href="urls.adminAuth"
          class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.adminLink }}</a
        >
      </li>
      <li>
        <a
          :href="urls.sitemap"
          class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
          >{{ translate.sitemapLink }}</a
        >
      </li>
      <li>
        <span class="text-slate-600 font-normal p-1.5">{{ translate.templateVersion }}</span>
      </li>
    </ul>

    <p class="text-slate-600 md:ml-auto">{{ translate.credit }}</p>
  </div>
</template>

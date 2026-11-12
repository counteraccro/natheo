<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pied de page du thème Natheo-Base : menu, texte et réseaux sociaux du site, langues, liens utiles
 */
import { computed, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import MenuList from '@/vue/Components/Front/NatheoBase/MenuList.vue';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { SiteOptions } from '@/ts/Front/Api/OptionSystem.type';

const props = defineProps<{ page: Page | null }>();
const { urls, datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(toRef(props, 'page'));

const SOCIALS: Record<string, keyof SiteOptions> = {
  Facebook: 'front_footer_social_facebook_url',
  Instagram: 'front_footer_social_instagram_url',
  GitHub: 'front_footer_social_github_url',
  TikTok: 'front_footer_social_tiktok_url',
  LinkedIn: 'front_footer_social_linkedin_url',
  YouTube: 'front_footer_social_youtube_url',
  X: 'front_footer_social_x_url',
};

const socials = computed(() =>
  Object.entries(SOCIALS).flatMap(([label, key]) => {
    const url = options.value?.[key];
    return typeof url === 'string' && url !== '' ? [{ label, url }] : [];
  })
);

const languages = computed(() => {
  const labels: Record<string, string> = {
    fr: translate.footer.frLink,
    en: translate.footer.enLink,
    es: translate.footer.esLink,
  };
  return datas.locales.map((locale) => ({ locale, label: labels[locale] ?? locale, url: urls.home[locale] }));
});
</script>

<template>
  <footer class="border-t border-gray-200 dark:border-gray-700 text-sm">
    <div class="max-w-5xl mx-auto px-4 py-6 space-y-4">
      <MenuList v-if="menus.position_footer" :elements="menus.position_footer.elements" horizontal />
      <p v-if="options?.front_footer_texte">{{ options.front_footer_texte }}</p>
      <ul v-if="socials.length" class="flex flex-wrap gap-4">
        <li v-for="social in socials" :key="social.label">
          <a :href="social.url" target="_blank" rel="noopener" class="underline">{{ social.label }}</a>
        </li>
      </ul>
      <ul class="flex flex-wrap gap-4">
        <li v-for="language in languages" :key="language.locale">
          <a :href="language.url" :hreflang="language.locale" :aria-current="language.locale === datas.locale">
            {{ language.label }}
          </a>
        </li>
        <li>
          <a :href="urls.sitemap" class="underline">{{ translate.footer.sitemapLink }}</a>
        </li>
        <li>
          <a :href="urls.adminAuth" class="underline">{{ translate.footer.adminLink }}</a>
        </li>
      </ul>
      <p class="text-gray-500">{{ translate.footer.credit }}</p>
    </div>
  </footer>
</template>

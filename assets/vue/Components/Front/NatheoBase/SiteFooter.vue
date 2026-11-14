<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pied de page du thème Natheo-Base : menu, texte et réseaux sociaux du site, langues, liens utiles
 */
import { toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import { useLanguages } from '@/vue/Composables/Front/useLanguages';
import { useSocialLinks } from '@/vue/Composables/Front/useSocialLinks';
import FooterMenu from '@/vue/Components/Front/NatheoBase/Menu/FooterMenu.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page | null }>();
const { urls, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(toRef(props, 'page'));

const socials = useSocialLinks();

const languages = useLanguages();
</script>

<template>
  <footer class="border-t border-gray-200 dark:border-gray-700 text-sm">
    <div class="max-w-5xl mx-auto px-4 py-6 space-y-4">
      <FooterMenu v-if="menus.footer" :menu="menus.footer" />
      <p v-if="options?.front_footer_texte">{{ options.front_footer_texte }}</p>
      <ul v-if="socials.length" class="flex flex-wrap gap-4">
        <li v-for="social in socials" :key="social.network">
          <a :href="social.url" target="_blank" rel="noopener" class="underline">{{ social.label }}</a>
        </li>
      </ul>
      <ul class="flex flex-wrap gap-4">
        <li v-for="language in languages" :key="language.locale">
          <a :href="language.url" :hreflang="language.locale" :aria-current="language.current">
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

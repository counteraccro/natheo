<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pied de page du thème Natheo-Horizon : nom et texte du site, réseaux sociaux, menu, langues et liens utiles
 */
import { toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useLanguages } from '@/vue/Composables/Front/useLanguages';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import { useSocialLinks, type SocialNetwork } from '@/vue/Composables/Front/useSocialLinks';
import BrandLogo from '@/vue/Components/Front/NatheoHorizon/Layout/BrandLogo.vue';
import HorizonIcon, { type IconName } from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import FooterMenu from '@/vue/Components/Front/NatheoHorizon/Menu/FooterMenu.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page | null }>();
const { urls, datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(toRef(props, 'page'));
const languages = useLanguages();

const socials = useSocialLinks();

// TikTok n'a pas d'icône dans Flowbite : globe générique
const ICONS: Record<SocialNetwork, IconName> = {
  github: 'github',
  linkedin: 'linkedin',
  youtube: 'youtube',
  x: 'x',
  facebook: 'facebook',
  instagram: 'instagram',
  tiktok: 'globe',
};
</script>

<template>
  <footer class="bg-night pt-16 pb-8 text-on-night [--focus:var(--focus-on-night)]">
    <div class="mx-auto w-full max-w-page px-5">
      <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
        <div>
          <a
            :href="urls.home[datas.locale]"
            class="inline-flex items-center gap-2.5 text-[1.6rem] font-extrabold tracking-[-0.03em] text-on-night no-underline"
          >
            <BrandLogo class="size-10" />
            {{ options?.site_name }}
          </a>
          <p v-if="options?.front_footer_texte" class="mt-4 max-w-[36ch] whitespace-pre-line text-on-night-muted">
            {{ options.front_footer_texte }}
          </p>
          <ul v-if="socials.length" class="mt-5 flex flex-wrap gap-1.5" :aria-label="translate.footer.socialLabel">
            <li v-for="social in socials" :key="social.network">
              <a
                :href="social.url"
                target="_blank"
                rel="noopener"
                class="grid size-11 place-items-center rounded-nh text-on-night shadow-[inset_0_0_0_1.5px_rgb(201_184_255/0.35)] hover:shadow-[inset_0_0_0_1.5px_#fff]"
              >
                <span class="sr-only">{{ social.label }}</span>
                <HorizonIcon :name="ICONS[social.network]" class="size-5.5" />
              </a>
            </li>
          </ul>
        </div>
        <FooterMenu v-if="menus.footer" :menu="menus.footer" />
      </div>

      <div
        class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-night-line pt-6 text-[0.9rem] text-on-night-muted"
      >
        <ul class="flex flex-wrap gap-x-5 gap-y-1">
          <li v-for="language in languages" :key="language.locale">
            <a
              :href="language.url"
              :lang="language.locale"
              :hreflang="language.locale"
              :aria-current="language.current ? 'true' : undefined"
              class="text-on-night-muted aria-[current=true]:font-bold aria-[current=true]:text-on-night aria-[current=true]:no-underline"
            >
              {{ language.label }}
            </a>
          </li>
          <li>
            <a :href="urls.sitemap" class="text-on-night-muted">{{ translate.footer.sitemapLink }}</a>
          </li>
          <li>
            <a :href="urls.adminAuth" class="text-on-night-muted">{{ translate.footer.adminLink }}</a>
          </li>
        </ul>
        <p>{{ translate.footer.credit }} · {{ translate.footer.templateVersion }}</p>
      </div>
    </div>
  </footer>
</template>

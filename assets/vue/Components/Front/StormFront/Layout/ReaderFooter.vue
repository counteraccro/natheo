<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pied de page en fin de lecture : texte du site, réseaux sociaux, menu, liens utiles, crédit et version du thème
 */
import { toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useConsent } from '@/vue/Composables/Front/useConsent';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import { useSocialLinks } from '@/vue/Composables/Front/useSocialLinks';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import FooterMenu from '@/vue/Components/Front/StormFront/Menu/FooterMenu.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page | null }>();
const { urls, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(toRef(props, 'page'));
const socials = useSocialLinks();
const consent = useConsent();
</script>

<template>
  <footer
    class="border-t border-line bg-thread px-[clamp(1.25rem,4vw,4.5rem)] pt-12 pb-[calc(2.25rem+64px)] text-[0.95rem] text-fg-muted md:pb-9"
  >
    <div class="grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,2.2fr)]">
      <div>
        <p v-if="options?.front_footer_texte" class="max-w-[34ch] text-[1.05rem] leading-relaxed whitespace-pre-line text-fg">
          {{ options.front_footer_texte }}
        </p>
        <ul v-if="socials.length" class="mt-4 flex flex-wrap gap-1.5" :aria-label="translate.footer.socialLabel">
          <li v-for="social in socials" :key="social.network">
            <a
              :href="social.url"
              target="_blank"
              rel="noopener"
              class="grid size-11 place-items-center rounded-sf text-fg shadow-[inset_0_0_0_1.5px_var(--line)] hover:shadow-[inset_0_0_0_1.5px_var(--fg)]"
            >
              <span class="sr-only">{{ social.label }}</span>
              <UiIcon :name="social.icon" class="size-5.5" />
            </a>
          </li>
        </ul>
      </div>
      <FooterMenu v-if="menus.footer" :menu="menus.footer" />
    </div>

    <div class="mt-10 flex flex-wrap justify-between gap-x-6 gap-y-3 border-t border-line pt-5">
      <ul class="flex flex-wrap gap-x-5 gap-y-1">
        <li><a :href="urls.sitemap" class="text-fg-muted hover:text-fg">{{ translate.footer.sitemapLink }}</a></li>
        <li><a :href="urls.adminAuth" class="text-fg-muted hover:text-fg">{{ translate.footer.adminLink }}</a></li>
        <li v-if="consent.required">
          <button type="button" class="cursor-pointer text-fg-muted underline hover:text-fg" @click="consent.reopen()">
            {{ translate.footer.consentLink }}
          </button>
        </li>
      </ul>
      <p>{{ translate.footer.credit }} · {{ translate.footer.templateVersion }}</p>
    </div>
  </footer>
</template>

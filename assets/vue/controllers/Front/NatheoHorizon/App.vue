<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Composant racine du thème Natheo-Horizon : installe le contexte du site public et compose la page
 */
import { computed } from 'vue';
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { usePage } from '@/vue/Composables/Front/usePage';
import ReadingProgress from '@/vue/Components/Front/NatheoHorizon/Layout/ReadingProgress.vue';
import SiteHeader from '@/vue/Components/Front/NatheoHorizon/Layout/SiteHeader.vue';
import SiteFooter from '@/vue/Components/Front/NatheoHorizon/Layout/SiteFooter.vue';
import PageSkeleton from '@/vue/Components/Front/NatheoHorizon/Layout/PageSkeleton.vue';
import ErrorState from '@/vue/Components/Front/NatheoHorizon/Layout/ErrorState.vue';
import SideMenu from '@/vue/Components/Front/NatheoHorizon/Menu/SideMenu.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
const { translate } = createFrontContext(props);
const { data: page, loading, error } = usePage();
const { menus } = useMenus(page);

const columns = computed(() => {
  if (menus.value.left && menus.value.right) {
    return 'lg:grid-cols-[240px_minmax(0,1fr)_240px]';
  }
  if (menus.value.left) {
    return 'lg:grid-cols-[240px_minmax(0,1fr)]';
  }
  return menus.value.right ? 'lg:grid-cols-[minmax(0,1fr)_240px]' : '';
});
</script>

<template>
  <a
    href="#contenu"
    class="absolute -top-16 left-4 z-100 rounded-nh bg-white px-4 py-3 font-semibold text-[#13112a] focus:top-4"
  >
    {{ translate.header.skipLink }}
  </a>
  <ReadingProgress />
  <SiteHeader :page="page" />
  <main id="contenu" tabindex="-1" class="outline-none">
    <PageSkeleton v-if="loading" />
    <ErrorState v-else-if="error" :error="error" />
    <template v-else-if="page">
      <section class="bg-night pt-10 pb-16 text-on-night [--focus:var(--focus-on-night)]">
        <div class="mx-auto w-full max-w-page px-5">
          <h1 class="max-w-[16ch] text-[clamp(2.4rem,5.2vw,4.25rem)] leading-[1.02]">{{ page.title }}</h1>
        </div>
      </section>
      <div class="mx-auto grid w-full max-w-page items-start gap-14 px-5 pt-16 pb-20" :class="columns">
        <SideMenu v-if="menus.left" :menu="menus.left" />
        <div class="min-w-0" />
        <SideMenu v-if="menus.right" :menu="menus.right" />
      </div>
    </template>
  </main>
  <SiteFooter :page="page" />
</template>

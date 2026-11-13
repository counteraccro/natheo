<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Composant racine du thème Natheo-Horizon : installe le contexte du site public et compose la page
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { usePage } from '@/vue/Composables/Front/usePage';
import ReadingProgress from '@/vue/Components/Front/NatheoHorizon/Layout/ReadingProgress.vue';
import SiteHeader from '@/vue/Components/Front/NatheoHorizon/Layout/SiteHeader.vue';
import SiteFooter from '@/vue/Components/Front/NatheoHorizon/Layout/SiteFooter.vue';
import PageSkeleton from '@/vue/Components/Front/NatheoHorizon/Layout/PageSkeleton.vue';
import ErrorState from '@/vue/Components/Front/NatheoHorizon/Layout/ErrorState.vue';
import PageView from '@/vue/Components/Front/NatheoHorizon/Page/PageView.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
const { translate } = createFrontContext(props);
const { data: page, loading, error } = usePage();
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
    <PageView v-else-if="page" :page="page" />
  </main>
  <SiteFooter :page="page" />
</template>

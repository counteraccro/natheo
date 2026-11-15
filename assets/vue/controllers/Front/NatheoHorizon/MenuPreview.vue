<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu dans le thème Natheo-Horizon, affiché par l'éditeur de menu : en-tête, colonne de côté ou pied de
 * page selon la position du menu
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenuPreview } from '@/vue/Composables/Front/useMenuPreview';
import SiteHeader from '@/vue/Components/Front/NatheoHorizon/Layout/SiteHeader.vue';
import SiteFooter from '@/vue/Components/Front/NatheoHorizon/Layout/SiteFooter.vue';
import SideMenu from '@/vue/Components/Front/NatheoHorizon/Menu/SideMenu.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const { menu, page } = useMenuPreview();

const LINES = ['w-2/5', 'w-[min(640px,90%)]', 'w-[min(560px,80%)]', 'w-[min(600px,85%)]', 'w-1/2'];
</script>

<template>
  <template v-if="menu">
    <template v-if="menu.position === 'header'">
      <SiteHeader :page="page" />
      <!-- Place laissée aux sous-menus, qui s'ouvrent par-dessus le contenu -->
      <div class="min-h-110" aria-hidden="true" />
    </template>
    <SiteFooter v-else-if="menu.position === 'footer'" :page="page" />
    <div
      v-else
      class="mx-auto grid w-full max-w-page items-start gap-10 px-5 py-12"
      :class="menu.position === 'left' ? 'lg:grid-cols-[220px_minmax(0,1fr)]' : 'lg:grid-cols-[minmax(0,1fr)_220px]'"
    >
      <SideMenu v-if="menu.position === 'left'" :menu="menu" />
      <div class="grid min-w-0 gap-4" aria-hidden="true">
        <div class="h-1 w-14 rounded-nh bg-surface" />
        <div v-for="width in LINES" :key="width" class="h-4.5 rounded-nh bg-surface" :class="width" />
      </div>
      <SideMenu v-if="menu.position === 'right'" :menu="menu" />
    </div>
  </template>
</template>

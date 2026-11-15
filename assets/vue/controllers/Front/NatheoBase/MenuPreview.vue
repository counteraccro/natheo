<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu dans le thème Natheo-Base, affiché par l'éditeur de menu (et par tout thème qui n'a pas son propre
 * aperçu) : en-tête, colonne de côté ou pied de page selon la position du menu
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenuPreview } from '@/vue/Composables/Front/useMenuPreview';
import SiteHeader from '@/vue/Components/Front/NatheoBase/SiteHeader.vue';
import SiteFooter from '@/vue/Components/Front/NatheoBase/SiteFooter.vue';
import SideMenu from '@/vue/Components/Front/NatheoBase/Menu/SideMenu.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const { menu, page } = useMenuPreview();

const LINES = ['w-2/5', 'w-11/12', 'w-4/5', 'w-5/6', 'w-1/2'];
</script>

<template>
  <template v-if="menu">
    <SiteHeader v-if="menu.position === 'header'" :page="page" />
    <SiteFooter v-else-if="menu.position === 'footer'" :page="page" />
    <div v-else class="w-full max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
      <aside class="md:w-56 shrink-0" :class="menu.position === 'left' ? 'md:order-1' : 'md:order-3'">
        <SideMenu :menu="menu" />
      </aside>
      <div class="flex-1 min-w-0 space-y-3 md:order-2" aria-hidden="true">
        <div v-for="width in LINES" :key="width" class="h-4 bg-gray-200 dark:bg-gray-700" :class="width" />
      </div>
    </div>
  </template>
</template>

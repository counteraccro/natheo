<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu dans le thème StormFront, affiché par l'éditeur de menu : barre latérale, colonne de côté ou pied
 * de page selon la position du menu
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenuPreview } from '@/vue/Composables/Front/useMenuPreview';
import Rail from '@/vue/Components/Front/StormFront/Layout/Rail.vue';
import ReaderFooter from '@/vue/Components/Front/StormFront/Layout/ReaderFooter.vue';
import SideMenu from '@/vue/Components/Front/StormFront/Menu/SideMenu.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const { menu, page } = useMenuPreview();

const LINES = ['w-2/5', 'w-[min(640px,90%)]', 'w-[min(560px,80%)]', 'w-[min(600px,85%)]', 'w-1/2'];
</script>

<template>
  <template v-if="menu">
    <!-- Hauteur fixe : la barre latérale prend sinon toute la hauteur du cadre, qui suit celle de la page -->
    <div
      v-if="menu.position === 'header'"
      class="h-160 md:grid md:grid-cols-[auto_minmax(0,1fr)] md:[&>div:first-child]:h-full"
    >
      <Rail :page="page" :has-thread="false" :thread-open="false" thread-id="" />
      <div class="grid content-start gap-4 px-[clamp(1.25rem,4vw,4.5rem)] pt-12" aria-hidden="true">
        <div class="mb-4 h-12 w-3/5 rounded-sf bg-thread" />
        <div v-for="width in LINES" :key="width" class="h-4.5 rounded-sf bg-thread" :class="width" />
      </div>
    </div>
    <ReaderFooter v-else-if="menu.position === 'footer'" :page="page" />
    <div
      v-else
      class="grid w-full items-start gap-16 px-[clamp(1.25rem,4vw,4.5rem)] py-12"
      :class="menu.position === 'left' ? 'xl:grid-cols-[220px_minmax(0,1fr)]' : 'xl:grid-cols-[minmax(0,1fr)_220px]'"
    >
      <SideMenu v-if="menu.position === 'left'" :menu="menu" />
      <div class="grid min-w-0 gap-4" aria-hidden="true">
        <div v-for="width in LINES" :key="width" class="h-4.5 rounded-sf bg-thread" :class="width" />
      </div>
      <SideMenu v-if="menu.position === 'right'" :menu="menu" />
    </div>
  </template>
</template>

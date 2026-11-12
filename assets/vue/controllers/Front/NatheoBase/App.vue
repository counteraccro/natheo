<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant racine du thème Natheo-Base : installe le contexte du site public et compose la page
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { usePage } from '@/vue/Composables/Front/usePage';
import SiteHeader from '@/vue/Components/Front/NatheoBase/SiteHeader.vue';
import SiteFooter from '@/vue/Components/Front/NatheoBase/SiteFooter.vue';
import PageView from '@/vue/Components/Front/NatheoBase/PageView.vue';
import SideMenu from '@/vue/Components/Front/NatheoBase/Menu/SideMenu.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
const context = createFrontContext(props);
const { data: page, loading, error } = usePage();
const { menus } = useMenus(page);
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <SiteHeader :page="page" />
    <div class="flex-1 w-full max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
      <aside v-if="menus.left" class="md:w-56 shrink-0 md:order-1">
        <SideMenu :menu="menus.left" />
      </aside>
      <main class="flex-1 min-w-0 md:order-2" :aria-busy="loading">
        <p v-if="loading">{{ translate.main.loading }}</p>
        <div v-else-if="error" role="alert">
          <p>{{ context.errorLabel(error) }}</p>
          <a :href="urls.home[datas.locale]" class="underline">{{ translate.errorApi.back }}</a>
        </div>
        <PageView v-else-if="page" :page="page" />
      </main>
      <aside v-if="menus.right" class="md:w-56 shrink-0 md:order-3">
        <SideMenu :menu="menus.right" />
      </aside>
    </div>
    <SiteFooter :page="page" />
  </div>
</template>

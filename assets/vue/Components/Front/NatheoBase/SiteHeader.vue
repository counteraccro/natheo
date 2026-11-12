<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête du thème Natheo-Base : nom du site, menu principal, connexion
 */
import { toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import MenuList from '@/vue/Components/Front/NatheoBase/MenuList.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page | null }>();
const { urls, datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(toRef(props, 'page'));
</script>

<template>
  <header class="border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-5xl mx-auto px-4 py-4 flex flex-wrap items-center gap-6">
      <a :href="urls.home[datas.locale]" class="text-xl font-semibold">{{ options?.site_name }}</a>
      <nav v-if="menus.position_header" class="flex-1" :aria-label="options?.site_name ?? ''">
        <MenuList :elements="menus.position_header.elements" horizontal />
      </nav>
      <div class="ml-auto flex items-center gap-3 text-sm">
        <template v-if="datas.user">
          <img v-if="datas.user.avatar" :src="datas.user.avatar" alt="" class="size-8 rounded-full" />
          <span>{{ datas.user.login }}</span>
          <a :href="urls.logout" class="underline">{{ translate.header.logout }}</a>
        </template>
        <a v-else :href="urls.adminAuth" class="underline">{{ translate.header.login }}</a>
      </div>
    </div>
  </header>
</template>

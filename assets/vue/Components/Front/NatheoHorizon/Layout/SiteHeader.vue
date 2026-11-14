<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête du thème Natheo-Horizon : logo et nom du site, menu, langues, mode sombre, utilisateur connecté
 */
import { computed, ref, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import BrandLogo from '@/vue/Components/Front/NatheoHorizon/Layout/BrandLogo.vue';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import LocaleSwitcher from '@/vue/Components/Front/NatheoHorizon/Layout/LocaleSwitcher.vue';
import MobileDrawer from '@/vue/Components/Front/NatheoHorizon/Layout/MobileDrawer.vue';
import ThemeToggle from '@/vue/Components/Front/NatheoHorizon/Layout/ThemeToggle.vue';
import UserAvatar from '@/vue/Components/Front/NatheoHorizon/Layout/UserAvatar.vue';
import HeaderMenu from '@/vue/Components/Front/NatheoHorizon/Menu/HeaderMenu.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page | null }>();
const { urls, datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus, layoutOf } = useMenus(toRef(props, 'page'));
const siteName = computed(() => options.value?.site_name ?? '');
const sidebar = computed(() => menus.value.header !== undefined && layoutOf(menus.value.header).kind === 'sidebar');
const drawerOpen = ref(false);
const burger = ref<HTMLButtonElement | null>(null);
</script>

<template>
  <header class="sticky top-0 z-40 bg-night text-on-night [--focus:var(--focus-on-night)]">
    <div class="mx-auto flex min-h-18 w-full max-w-page items-center gap-6 px-5">
      <a
        :href="urls.home[datas.locale]"
        class="inline-flex items-center gap-2.5 text-[1.3rem] font-extrabold tracking-[-0.03em] text-on-night no-underline"
      >
        <BrandLogo class="size-7.5" />
        {{ siteName }}
      </a>
      <HeaderMenu v-if="menus.header && !sidebar" :menu="menus.header" />
      <div class="ml-auto flex items-center gap-2">
        <LocaleSwitcher v-if="datas.locales.length > 1" class="hidden lg:block" />
        <ThemeToggle />
        <div v-if="datas.user" class="hidden items-center gap-2.5 text-[0.95rem] lg:flex">
          <UserAvatar :user="datas.user" />
          <span>{{ datas.user.login }}</span>
          <a
            :href="urls.logout"
            class="inline-flex min-h-11 items-center rounded-nh px-4 font-semibold text-on-night no-underline shadow-[inset_0_0_0_1.5px_rgb(201_184_255/0.55)] hover:shadow-[inset_0_0_0_1.5px_#fff]"
          >
            {{ translate.header.logout }}
          </a>
        </div>
        <button
          ref="burger"
          type="button"
          class="inline-flex size-11 cursor-pointer items-center justify-center rounded-nh hover:bg-white/10"
          :class="{ 'lg:hidden': !sidebar }"
          :aria-expanded="drawerOpen"
          aria-controls="nh-drawer"
          @click="drawerOpen = true"
        >
          <span class="sr-only">{{ translate.header.openMenu }}</span>
          <HorizonIcon name="bars" class="size-6" />
        </button>
      </div>
    </div>
  </header>
  <MobileDrawer
    id="nh-drawer"
    v-model:open="drawerOpen"
    :menu="menus.header ?? null"
    :site-name="siteName"
    @closed="burger?.focus()"
  />
</template>

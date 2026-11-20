<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Barre latérale du thème StormFront, à la place d'un en-tête : logo et nom du site, menu d'en-tête, langue, mode,
 * session ; rail d'icônes sur écran moyen, barre en bas sur mobile
 */
import { computed, nextTick, ref, toRef, watch } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useColorScheme } from '@/vue/Composables/Front/useColorScheme';
import { useLanguages } from '@/vue/Composables/Front/useLanguages';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import BrandLogo from '@/vue/Components/Global/Ui/BrandLogo.vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import RailSheet from '@/vue/Components/Front/StormFront/Layout/RailSheet.vue';
import MegaColumns from '@/vue/Components/Front/StormFront/Menu/MegaColumns.vue';
import MenuTree from '@/vue/Components/Front/StormFront/Menu/MenuTree.vue';
import RailMenu from '@/vue/Components/Front/StormFront/Menu/RailMenu.vue';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ page: Page | null; hasThread: boolean; threadOpen: boolean; threadId: string }>();
const emit = defineEmits<{ toggleThread: [] }>();
const { urls, datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus, layoutOf } = useMenus(toRef(props, 'page'));
const { scheme, toggle: toggleScheme } = useColorScheme();
const languages = useLanguages();

const SHEET_ID = 'sf-sheet';
const sheet = ref<string | null>(null);
const megaElement = ref<MenuElement | null>(null);
let opener: HTMLElement | null = null;

const headerLayout = computed(() => (menus.value.header ? layoutOf(menus.value.header) : null));
const wide = computed(
  () => sheet.value?.startsWith('mega-') === true || (sheet.value === 'menu' && headerLayout.value?.kind === 'mega')
);
const sheetTitle = computed(() => {
  if (sheet.value === 'menu') {
    return translate.header.menuButton;
  }
  return sheet.value === 'lang' ? translate.header.localeLabel : (megaElement.value?.label ?? '');
});
const homeUrl = computed(() => urls.home[datas.locale] ?? '/');

function toggle(key: string, element: HTMLElement): void {
  if (sheet.value === key) {
    close();
    return;
  }
  opener = element;
  sheet.value = key;
  if (props.threadOpen) {
    emit('toggleThread');
  }
}

function openMega(element: MenuElement, button: HTMLElement): void {
  megaElement.value = element;
  toggle(`mega-${element.id}`, button);
}

function close(): void {
  const button = opener;
  sheet.value = null;
  opener = null;
  nextTick(() => button?.focus());
}

function toggleThread(): void {
  sheet.value = null;
  emit('toggleThread');
}

// Une autre page ouverte sans rechargement ferme le volet
watch(
  () => props.page?.id,
  () => {
    sheet.value = null;
  }
);

const BUTTON =
  'grid w-16 cursor-pointer justify-items-center gap-1 rounded-sf py-2 text-[0.74rem] leading-tight font-medium text-on-rail-muted no-underline hover:bg-white/6 hover:text-on-rail aria-expanded:bg-white/6 aria-expanded:text-on-rail aria-expanded:shadow-[inset_0_3px_0_var(--bolt)] md:aria-expanded:shadow-[inset_3px_0_0_var(--bolt)] xl:w-auto xl:flex-1';
</script>

<template>
  <div
    class="fixed inset-x-0 bottom-0 z-30 flex h-16 items-center justify-around bg-rail px-2 pb-[env(safe-area-inset-bottom)] text-on-rail [--focus:var(--bolt)] md:relative md:inset-auto md:h-dvh md:flex-col md:justify-start md:gap-1.5 md:overflow-y-auto md:px-0 md:py-4 xl:items-stretch xl:gap-0 xl:pt-6 xl:pb-4"
  >
    <a
      :href="homeUrl"
      class="mb-8 hidden items-center gap-2.5 px-5 text-[1.3rem] leading-tight font-semibold tracking-[-0.03em] text-on-rail no-underline xl:flex"
    >
      <BrandLogo class="size-7.5 shrink-0" />
      {{ options?.site_name }}
    </a>
    <a :href="homeUrl" class="mb-4 hidden size-13 place-items-center rounded-sf md:grid xl:hidden">
      <BrandLogo class="size-9" />
      <span class="sr-only">{{ options?.site_name }}</span>
    </a>

    <RailMenu
      v-if="menus.header"
      :menu="menus.header"
      :sheet="sheet"
      :sheet-id="SHEET_ID"
      class="hidden xl:block"
      @mega="openMega"
    />

    <button
      v-if="menus.header"
      type="button"
      :class="BUTTON"
      class="xl:hidden"
      :aria-expanded="sheet === 'menu'"
      :aria-controls="SHEET_ID"
      @click="toggle('menu', $event.currentTarget as HTMLElement)"
    >
      <UiIcon name="bars" class="size-5.5" />
      {{ translate.header.menuButton }}
    </button>
    <button
      v-if="hasThread"
      type="button"
      :class="BUTTON"
      class="md:hidden"
      :aria-expanded="threadOpen"
      :aria-controls="threadId"
      @click="toggleThread"
    >
      <UiIcon name="list" class="size-5.5" />
      {{ translate.thread.button }}
    </button>

    <div
      class="contents md:mt-auto md:flex md:flex-col md:items-center md:gap-1.5 xl:flex-row xl:items-stretch xl:justify-between xl:gap-0 xl:border-t xl:border-rail-line xl:px-3 xl:pt-4"
    >
      <button
        v-if="datas.locales.length > 1"
        type="button"
        :class="BUTTON"
        :aria-expanded="sheet === 'lang'"
        :aria-controls="SHEET_ID"
        @click="toggle('lang', $event.currentTarget as HTMLElement)"
      >
        <UiIcon name="globe" class="size-5.5" />
        <span><span class="sr-only">{{ translate.header.localeLabel }} : </span>{{ datas.locale.toUpperCase() }}</span>
      </button>
      <button type="button" :class="BUTTON" :aria-pressed="scheme === 'dark'" @click="toggleScheme">
        <UiIcon :name="scheme === 'dark' ? 'sun' : 'moon'" class="size-5.5" />
        <span aria-hidden="true">{{ translate.header.modeButton }}</span>
        <span class="sr-only">{{ translate.header.darkMode }}</span>
      </button>
      <a v-if="datas.user" :href="urls.logout" :class="BUTTON">
        <img v-if="datas.user.avatar" :src="datas.user.avatar" alt="" class="size-6 rounded-full object-cover" />
        <span
          v-else
          class="grid size-6 place-items-center rounded-full bg-bolt text-[0.8rem] font-bold text-on-bolt"
          aria-hidden="true"
        >
          {{ datas.user.login.slice(0, 1).toUpperCase() }}
        </span>
        {{ translate.header.logout }}
        <span class="sr-only">({{ datas.user.login }})</span>
      </a>
    </div>

    <RailSheet v-if="sheet" :id="SHEET_ID" :title="sheetTitle" :wide="wide" @close="close">
      <MenuTree v-if="sheet === 'menu' && menus.header" :menu="menus.header" />
      <ul v-else-if="sheet === 'lang'" class="grid grid-cols-3 gap-1.5" :aria-label="translate.header.localeLabel">
        <li v-for="language in languages" :key="language.locale">
          <a
            :href="language.url"
            :lang="language.locale"
            :hreflang="language.locale"
            :aria-current="language.current ? 'true' : undefined"
            class="flex min-h-11 items-center justify-center rounded-sf px-2 font-semibold text-on-rail no-underline shadow-[inset_0_0_0_1.5px_var(--rail-line)] hover:shadow-[inset_0_0_0_1.5px_var(--on-rail)] aria-[current=true]:bg-on-rail aria-[current=true]:text-rail aria-[current=true]:shadow-none"
          >
            {{ language.label }}
          </a>
        </li>
      </ul>
      <MegaColumns v-else-if="megaElement && headerLayout" :element="megaElement" :columns="headerLayout.columns" />
    </RailSheet>
  </div>
</template>

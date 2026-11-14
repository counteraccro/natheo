<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page affichée : bandeau, menus de côté, blocs selon leur taille et commentaires
 */
import { computed, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { usePageBlocks, type BlockSize } from '@/vue/Composables/Front/usePageBlocks';
import { useReadingTime } from '@/vue/Composables/Front/useReadingTime';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import PageHero from '@/vue/Components/Front/NatheoHorizon/Page/PageHero.vue';
import SideMenu from '@/vue/Components/Front/NatheoHorizon/Menu/SideMenu.vue';
import BlockText from '@/vue/Components/Front/NatheoHorizon/Block/BlockText.vue';
import BlockFaq from '@/vue/Components/Front/NatheoHorizon/Block/BlockFaq.vue';
import BlockListing from '@/vue/Components/Front/NatheoHorizon/Block/BlockListing.vue';
import CommentSection from '@/vue/Components/Front/NatheoHorizon/Comment/CommentSection.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page }>();
const page = toRef(props, 'page');
const { datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(page);
const { blocks } = usePageBlocks(page, { text: BlockText, faq: BlockFaq, listing: BlockListing });
const readingTime = useReadingTime(blocks);

/** Largeur dans la grille de 6 colonnes et couleur de la barre du haut */
const SIZES: Record<BlockSize, string> = {
  full: 'col-span-6 before:bg-mod-full',
  half: 'col-span-6 md:col-span-3 before:bg-mod-half',
  third: 'col-span-6 md:col-span-2 before:bg-mod-third',
};

// Avec deux menus, celui de droite passe sous le contenu jusqu'à 1280 px
const columns = computed(() => {
  if (menus.value.left && menus.value.right) {
    return 'lg:grid-cols-[220px_minmax(0,1fr)] xl:grid-cols-[220px_minmax(0,1fr)_220px]';
  }
  if (menus.value.left) {
    return 'lg:grid-cols-[220px_minmax(0,1fr)]';
  }
  return menus.value.right ? 'lg:grid-cols-[minmax(0,1fr)_220px]' : '';
});

// Article : une seule colonne de lecture, sauf si un listing a besoin de la largeur
const reading = computed(
  () => blocks.value.length > 0 && blocks.value.every((block) => block.size === 'full' && block.type !== 'listing')
);

const showComments = computed(() => options.value?.open_comment === true && props.page.openComment && !datas.preview);
</script>

<template>
  <PageHero :page="page" :reading-time="readingTime" />
  <div class="mx-auto grid w-full max-w-page items-start gap-10 px-5 pt-16 pb-20" :class="columns">
    <SideMenu v-if="menus.left" :menu="menus.left" />
    <div class="min-w-0" :class="{ 'mx-auto w-full max-w-[70ch]': reading }">
      <div class="grid grid-cols-6 gap-x-10 gap-y-14">
        <section
          v-for="block in blocks"
          :key="block.id"
          class="relative min-w-0 pt-6 before:absolute before:top-0 before:left-0 before:h-1 before:w-14 before:rounded-sm before:content-['']"
          :class="SIZES[block.size]"
        >
          <component :is="block.component" v-if="block.content?.content" :content="block.content.content"
            v-bind="block.type === 'listing' ? { size: block.size } : {}"
          />
          <p v-else-if="block.error" role="alert" class="text-fg-muted">{{ translate.errorApi.default }}</p>
        </section>
      </div>
      <CommentSection v-if="showComments" :page="page" />
    </div>
    <SideMenu
      v-if="menus.right"
      :menu="menus.right"
      :class="{ 'lg:col-start-2 xl:col-start-auto': menus.left }"
    />
  </div>
</template>

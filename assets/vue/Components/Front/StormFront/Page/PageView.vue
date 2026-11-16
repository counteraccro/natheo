<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page ouverte dans le volet de lecture : en-tête, menus de côté, blocs selon leur taille et commentaires
 */
import { computed, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import { usePageBlocks, type BlockSize } from '@/vue/Composables/Front/usePageBlocks';
import { useReadingTime } from '@/vue/Composables/Front/useReadingTime';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import PageHeader from '@/vue/Components/Front/StormFront/Page/PageHeader.vue';
import SideMenu from '@/vue/Components/Front/StormFront/Menu/SideMenu.vue';
import BlockText from '@/vue/Components/Front/StormFront/Block/BlockText.vue';
import BlockFaq from '@/vue/Components/Front/StormFront/Block/BlockFaq.vue';
import BlockListing from '@/vue/Components/Front/StormFront/Block/BlockListing.vue';
import CommentSection from '@/vue/Components/Front/StormFront/Comment/CommentSection.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page }>();
const page = toRef(props, 'page');
const { datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const { menus } = useMenus(page);
const { blocks } = usePageBlocks(page, { text: BlockText, faq: BlockFaq, listing: BlockListing });
const readingTime = useReadingTime(blocks);

const SIZES: Record<BlockSize, string> = {
  full: 'col-span-6',
  half: 'col-span-6 lg:col-span-3',
  third: 'col-span-6 lg:col-span-2',
};

// Les menus de côté passent sous le texte jusqu'à 1280 px
const columns = computed(() => {
  if (menus.value.left && menus.value.right) {
    return 'xl:grid-cols-[200px_minmax(0,1fr)_200px]';
  }
  if (menus.value.left) {
    return 'xl:grid-cols-[220px_minmax(0,1fr)]';
  }
  return menus.value.right ? 'xl:grid-cols-[minmax(0,1fr)_220px]' : '';
});

const showComments = computed(() => options.value?.open_comment === true && props.page.openComment && !datas.preview);
</script>

<template>
  <div class="px-[clamp(1.25rem,4vw,4.5rem)] pb-20">
    <PageHeader :page="page" :reading-time="readingTime" />
    <div class="mt-12 grid items-start gap-16" :class="columns">
      <SideMenu v-if="menus.left" :menu="menus.left" />
      <div class="min-w-0">
        <div class="grid grid-cols-6 gap-x-10 gap-y-14">
          <section v-for="block in blocks" :key="block.id" class="min-w-0" :class="SIZES[block.size]">
            <component
              :is="block.component"
              v-if="block.content?.content"
              :content="block.content.content"
              v-bind="block.type === 'listing' ? { size: block.size } : {}"
            />
            <p v-else-if="block.error" role="alert" class="text-fg-muted">{{ translate.errorApi.default }}</p>
          </section>
        </div>
        <CommentSection v-if="showComments" :page="page" />
      </div>
      <SideMenu v-if="menus.right" :menu="menus.right" />
    </div>
  </div>
</template>

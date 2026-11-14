<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page du thème Natheo-Base : fil d'Ariane, en-tête, blocs de contenu, tags et commentaires
 */
import { computed, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import { useBreadcrumb } from '@/vue/Composables/Front/useBreadcrumb';
import { useEditLink } from '@/vue/Composables/Front/useEditLink';
import { usePageBlocks, type BlockSize } from '@/vue/Composables/Front/usePageBlocks';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import BlockText from '@/vue/Components/Front/NatheoBase/Block/BlockText.vue';
import BlockFaq from '@/vue/Components/Front/NatheoBase/Block/BlockFaq.vue';
import BlockListing from '@/vue/Components/Front/NatheoBase/Block/BlockListing.vue';
import PageComments from '@/vue/Components/Front/NatheoBase/PageComments.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page }>();
const page = toRef(props, 'page');
const { datas, translate } = useFrontContext();
const { data: options } = useSiteOptions();
const breadcrumb = useBreadcrumb(page);
const editLink = useEditLink();
const { blocks } = usePageBlocks(page, { text: BlockText, faq: BlockFaq, listing: BlockListing });

const SIZES: Record<BlockSize, string> = {
  full: 'md:col-span-6',
  half: 'md:col-span-3',
  third: 'md:col-span-2',
};

const showComments = computed(() => options.value?.open_comment === true && props.page.openComment);
const format = useFormat();
const updatedAt = computed(() => format.date(props.page.updatedAt, 'short'));
</script>

<template>
  <article class="space-y-6">
    <nav :aria-label="translate.main.breadcrumb">
      <ol class="flex flex-wrap gap-2 text-sm text-gray-500">
        <li v-for="(item, index) in breadcrumb" :key="index" class="flex gap-2">
          <span v-if="index > 0" aria-hidden="true">/</span>
          <a v-if="item.url" :href="item.url" class="underline">{{ item.label }}</a>
          <span v-else aria-current="page">{{ item.label }}</span>
        </li>
      </ol>
    </nav>

    <p v-if="datas.preview" role="status" class="p-3 border border-amber-400 bg-amber-50 dark:bg-amber-950">
      {{ translate.main.articleFooter.infoDraft }}
    </p>

    <header class="space-y-2">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <h1 class="text-3xl font-semibold">{{ page.title }}</h1>
        <a v-if="editLink" :href="editLink" class="text-sm underline">{{ translate.main.editLink }}</a>
      </div>
      <p class="text-sm text-gray-500">
        <span v-if="page.author">{{ translate.main.listing.by }} {{ page.author.name }}</span>
        <span v-if="updatedAt"> · {{ translate.main.articleFooter.edit }} {{ updatedAt }}</span>
        <span> · {{ page.reads }} {{ translate.main.articleFooter.statPublication }}</span>
      </p>
      <img
        v-if="page.headerImage"
        :src="page.headerImage"
        :alt="translate.main.headerImgAlt"
        class="w-full max-h-96 object-cover"
      />
    </header>

    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
      <section v-for="block in blocks" :key="block.id" :class="SIZES[block.size]">
        <component :is="block.component" v-if="block.content" :content="block.content.content" />
        <p v-else-if="block.error" role="alert">{{ translate.errorApi.default }}</p>
      </section>
    </div>

    <ul v-if="page.tags.length" class="flex flex-wrap gap-2">
      <li
        v-for="tag in page.tags"
        :key="tag.id"
        class="px-2 py-0.5 text-sm rounded text-white"
        :style="{ backgroundColor: tag.color }"
      >
        {{ tag.label }}
      </li>
    </ul>

    <PageComments v-if="showComments && !datas.preview" :page="page" />
  </article>
</template>

<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Structure d'une page : en-tête, blocs de contenu selon le rendu, auteur, tags et commentaires
 */
import { defineComponent, markRaw, type Component, type PropType } from 'vue';
import ContentText from './ContentText.vue';
import ContentFaq from './ContentFaq.vue';
import ContentListing from './ContentListing.vue';
import ContentComment from './ContentComment.vue';
import Breadcrumb from './Breadcrumb.vue';
import { PageRender } from '@/utils/Front/Const/PageRender';
import { PageStatus } from '@/utils/Front/Const/PageStatus';
import { ContentType } from '@/utils/Front/Const/ContentType';
import type { FrontApi } from '@/utils/Front/FrontApi';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type {
  FrontApiError,
  FrontMainTranslate,
  FrontPage,
  FrontPageContent,
} from '@/ts/Front/NatheoHorizon/Structure.type';

interface RenderLayout {
  gap: string;
  columns: string[];
}

const FULL = 'col-span-12 mb-3';
const HALF = 'col-span-12 lg:col-span-6';
const THIRD = 'col-span-12 lg:col-span-4';

/** Disposition des blocs pour chaque rendu de page : une classe par bloc, dans l'ordre des contenus */
const LAYOUTS: Record<number, RenderLayout> = {
  [PageRender.oneBlock]: { gap: '', columns: [FULL] },
  [PageRender.twoBlock]: { gap: 'gap-5', columns: [HALF, HALF] },
  [PageRender.threeBlock]: { gap: 'gap-5', columns: [THIRD, THIRD, THIRD] },
  [PageRender.twoBlockBottom]: { gap: '', columns: [FULL, FULL] },
  [PageRender.threeBlockBottom]: { gap: '', columns: [FULL, FULL, FULL] },
  [PageRender.oneTwoBlock]: { gap: 'gap-5', columns: [FULL, HALF, HALF] },
  [PageRender.twoOneBlock]: { gap: 'gap-5', columns: [HALF, HALF, FULL] },
  [PageRender.twoTwoBlock]: { gap: 'gap-4', columns: [HALF, HALF, HALF, HALF] },
};

const COMPONENTS: Record<number, Component> = {
  [ContentType.text]: markRaw(ContentText),
  [ContentType.faq]: markRaw(ContentFaq),
  [ContentType.listing]: markRaw(ContentListing),
};

interface RenderBlock {
  content: FrontPageContent;
  component: Component;
  css: string;
}

export default defineComponent({
  name: 'ContentStructure',
  components: { Breadcrumb, ContentComment },
  props: {
    page: {
      type: Object as PropType<FrontPage>,
      required: true,
    },
    frontApi: {
      type: Object as PropType<FrontApi>,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontMainTranslate>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
  },
  emits: {
    'api-failure': (_error: FrontApiError) => true,
  },
  computed: {
    layout(): RenderLayout {
      return LAYOUTS[this.page.render] ?? LAYOUTS[PageRender.oneBlock];
    },

    blocks(): RenderBlock[] {
      const blocks: RenderBlock[] = [];
      this.layout.columns.forEach((css, index) => {
        const content = this.page.contents[index];
        const component = content ? COMPONENTS[content.type] : undefined;
        if (content && component) {
          blocks.push({ content, component, css });
        }
      });
      return blocks;
    },

    isDraft(): boolean {
      return this.page.status === PageStatus.draft;
    },

    authorAvatar(): string | null {
      return this.page.author.avatar ? this.utilsFront.datas.pathAvatar + this.page.author.avatar : null;
    },

    updateIso(): string {
      return new Date(this.page.update * 1000).toISOString();
    },

    showComments(): boolean {
      return this.utilsFront.optionsSystem.OS_OPEN_COMMENT === '1' && this.page.openComment;
    },
  },
  methods: {
    apiFailure(error: FrontApiError): void {
      this.$emit('api-failure', error);
    },
  },
});
</script>

<template>
  <div class="relative w-full h-48 sm:h-56 md:h-72 lg:h-80">
    <img
      v-if="page.headerImg"
      :src="page.headerImg"
      :alt="translate.headerImgAlt"
      class="w-full h-full object-cover rounded-t-2xl"
    />
    <div
      class="absolute inset-0 bg-linear-to-t to-transparent rounded-t-2xl"
      :class="
        page.headerImg
          ? 'from-gray-800/70 via-gray-400/30'
          : 'from-theme-4-750/70 via-theme-4-750/30 border-l border-l-theme-4-750/20 border-r border-r-theme-4-750/20 border-t border-t-blue-100'
      "
    ></div>
    <h1
      class="absolute inset-0 flex items-center justify-center text-white text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-bold text-center px-4"
    >
      {{ page.title }}
    </h1>
  </div>

  <div v-if="isDraft" class="border border-red-300/50 bg-red-600/10 px-4 py-2 text-slate-600">
    <p class="text-center font-medium">
      {{ translate.articleFooter.infoDraft }}
    </p>
  </div>

  <div class="p-4 rounded-b-2xl bg-white">
    <Breadcrumb :page="page" :utils-front="utilsFront" :label="translate.breadcrumb" />

    <div class="grid grid-cols-12" :class="layout.gap">
      <div v-for="block in blocks" :key="block.content.id" :class="block.css">
        <component
          :is="block.component"
          :data="block.content"
          :utils-front="utilsFront"
          :locale="locale"
          :front-api="frontApi"
          :translate="translate"
          @api-failure="apiFailure"
        />
      </div>
    </div>

    <div class="m-3 mt-12 border-t border-neutral-200/70 dark:border-neutral-800/70 pt-8">
      <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 sm:items-start">
        <div>
          <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
            <div class="shrink-0">
              <img
                v-if="authorAvatar"
                :src="authorAvatar"
                :alt="page.author.author"
                class="size-14 rounded-full object-cover"
              />
              <div
                v-else
                class="flex size-14 items-center justify-center rounded-full bg-theme-4-750 dark:bg-gray-600 text-white font-semibold ring-1 ring-neutral-200/70 dark:ring-neutral-800/70"
                aria-hidden="true"
              >
                {{ page.author.author.substring(0, 1).toUpperCase() }}
              </div>
            </div>

            <h3 class="mt-2 sm:mt-0 text-base font-semibold text-slate-900">
              {{ page.author.author }}
            </h3>
          </div>

          <p class="mt-2 ml-3 text-sm text-slate-600">
            {{ page.author.description }}
          </p>
        </div>

        <div class="sm:text-right">
          <time :datetime="updateIso" class="block text-sm text-slate-600">
            {{ translate.articleFooter.edit }} {{ utilsFront.formatDate(page.update * 1000) }}
          </time>
          <p class="mt-2 text-sm text-slate-600">
            {{ page.statistiques?.PAGE_NB_READ }} {{ translate.articleFooter.statPublication }}
          </p>
        </div>
      </div>

      <div v-if="page.tags?.length" class="mt-6">
        <ul class="flex flex-wrap gap-2 justify-end">
          <li v-for="tag in page.tags" :key="tag.label">
            <span
              class="inline-flex items-center rounded-full border border-neutral-200/70 px-3 py-1 text-sm text-neutral-700"
            >
              {{ tag.label }}
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div v-if="showComments" class="p-4 rounded-2xl bg-white mt-3">
    <ContentComment
      :utils-front="utilsFront"
      :front-api="frontApi"
      :translate="translate.articleFooter.comment"
      :locale="locale"
      :slug="page.slug"
      @api-failure="apiFailure"
    />
  </div>
</template>

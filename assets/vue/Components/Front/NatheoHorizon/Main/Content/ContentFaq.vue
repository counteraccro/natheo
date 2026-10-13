<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Contenu de type Faq
 */
import { defineComponent, type PropType } from 'vue';
import { renderMarkdown } from '@/ts/MarkdownEditor/markdownRender';
import { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontApi } from '@/utils/Front/FrontApi';
import type { FrontApiError, FrontPageContent } from '@/ts/Front/NatheoHorizon/Structure.type';
import type { ContentFaqResponse, FaqCategory } from '@/ts/Front/NatheoHorizon/ContentFaq.type';

export default defineComponent({
  name: 'ContentFaq',
  // ContentStructure passe les mêmes props à tous les types de contenu
  inheritAttrs: false,
  props: {
    data: {
      type: Object as PropType<FrontPageContent>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    frontApi: {
      type: Object as PropType<FrontApi>,
      required: true,
    },
  },
  emits: {
    'api-failure': (_error: FrontApiError) => true,
  },
  data() {
    return {
      isLoad: false,
      categories: [] as FaqCategory[],
    };
  },
  created() {
    this.loadContent();
  },
  methods: {
    output(content: string): string {
      return renderMarkdown(content);
    },

    loadContent(): void {
      this.frontApi
        .getContentPage<ContentFaqResponse>({ id: this.data.id, locale: this.locale })
        .then((response) => {
          this.categories = response.content.categories;
          this.isLoad = true;
          this.$nextTick(() => UtilsFront.addCodeLineNumbers(this.$refs.content as HTMLElement));
        })
        .catch((error: FrontApiError) => this.$emit('api-failure', error));
    },
  },
});
</script>

<template>
  <div v-if="isLoad" ref="content">
    <div v-for="category in categories" :key="category.title" class="space-y-4">
      <h2 class="text-slate-900 text-2xl font-semibold mb-3 mt-3">{{ category.title }}</h2>
      <details
        v-for="question in category.questions"
        :key="question.title"
        class="group [&_summary::-webkit-details-marker]:hidden"
      >
        <summary
          class="flex items-center justify-between gap-1.5 rounded-md border border-gray-200 bg-white shadow-sm p-4 text-gray-900 group-open:bg-theme-4-750 group-open:text-theme-1-100! group-open:dark:bg-gray-600 hover:bg-theme-4-750 hover:text-theme-1-100! hover:cursor-pointer hover:dark:bg-gray-600"
        >
          <h3 class="text-lg">{{ question.title }}</h3>
          <svg
            class="size-5 shrink-0 transition-transform duration-300 group-open:-rotate-180"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </summary>
        <div class="px-4 pt-4 text-slate-600 natheo-content-text" v-html="output(question.answer)"></div>
      </details>
    </div>
  </div>
  <div v-else>
    <div class="space-y-6 p-4">
      <!-- Titre de la page -->
      <div class="h-8 w-1/4 rounded bg-gray-200 animate-pulse"></div>

      <!-- Bloc FAQ -->
      <div class="space-y-4">
        <!-- Question -->
        <div class="h-6 w-2/3 rounded bg-gray-200 animate-pulse"></div>
        <!-- Réponse -->
        <div class="space-y-2">
          <div class="h-4 w-full rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-5/6 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-2/3 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="h-6 w-1/2 rounded bg-gray-200 animate-pulse"></div>
        <div class="space-y-2">
          <div class="h-4 w-full rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-4/5 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-4 w-2/3 rounded bg-gray-200 animate-pulse"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Contenu de type texte
 */
import { defineComponent, type PropType } from 'vue';
import { renderMarkdown } from '@/ts/MarkdownEditor/markdownRender';
import { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontApi } from '@/utils/Front/FrontApi';
import type { FrontApiError, FrontPageContent } from '@/ts/Front/NatheoHorizon/Structure.type';
import type { ContentTextResponse } from '@/ts/Front/NatheoHorizon/ContentText.type';

export default defineComponent({
  name: 'ContentText',
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
      content: '',
      isLoad: false,
    };
  },
  computed: {
    output(): string {
      return renderMarkdown(this.content);
    },
  },
  created() {
    this.loadContent();
  },
  methods: {
    loadContent(): void {
      this.frontApi
        .getContentPage<ContentTextResponse>({ id: this.data.id, locale: this.locale })
        .then((response) => {
          this.content = response.content;
          this.isLoad = true;
          this.$nextTick(() => UtilsFront.addCodeLineNumbers(this.$refs.content as HTMLElement));
        })
        .catch((error: FrontApiError) => this.$emit('api-failure', error));
    },
  },
});
</script>

<template>
  <div v-if="isLoad" ref="content" class="natheo-content-text" v-html="output"></div>
  <div v-else>
    <div class="mx-auto w-full rounded-md p-4">
      <div class="flex flex-col sm:flex-row animate-pulse gap-4">
        <div class="size-10 rounded-full bg-gray-200 mx-auto sm:mx-0"></div>
        <div class="flex-1 space-y-6 py-1">
          <div class="h-2 rounded bg-gray-200"></div>

          <div class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="sm:col-span-2 h-2 rounded bg-gray-200"></div>
              <div class="sm:col-span-1 h-2 rounded bg-gray-200"></div>
            </div>

            <div class="h-2 rounded bg-gray-200"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>

            <div class="mt-6"></div>
            <div class="h-4 bg-gray-200 rounded w-full mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2 w-full"></div>

            <div class="mt-6"></div>
            <div class="h-4 bg-gray-200 rounded w-full mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2"></div>
            <div class="h-2 bg-gray-200 rounded mt-2 w-full"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

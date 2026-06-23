<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Page, PageData, PageTranslationItem, PageTranslations } from '@/ts/Page/type';

export default defineComponent({
  name: 'PageContent',
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    currentLocale: {
      type: String as PropType<string>,
      required: true,
    },
    pageDatas: {
      type: Object as PropType<PageData>,
      required: true,
    },
  },
  computed: {
    currentTranslation(): PageTranslationItem | undefined {
      return this.page.pageTranslations.find((t) => t.locale === this.currentLocale);
    },
    currentTitre(): string {
      return this.currentTranslation?.titre ?? '';
    },
    currentUrl(): string {
      return this.currentTranslation?.url ?? '';
    },
  },
});
</script>

<template>
  <div class="card rounded-lg relative overflow-visible mb-5">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
      <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 6h16M4 10h16M4 14h16M4 18h16"
        ></path>
      </svg>
      <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
        translate.page_content_form.title
      }}</span>
    </div>

    <div class="p-4">
      <div class="form-group">
        <label for="list-render-page" class="form-label">{{ translate.page_content_form.list_categories_label }}</label>
        <select id="list-render-page" class="form-input" v-model="page.category">
          <option v-for="(value, key) in pageDatas.list_categories" :value="parseInt(key)">{{ value }}</option>
        </select>
        <div id="list-status-help" class="form-text">{{ translate.page_content_form.list_categories_help }}</div>
      </div>
    </div>
  </div>

  <div class="card rounded-lg relative overflow-visible mb-5">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
      <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5.005 11.19V12l6.998 4.042L19 12v-.81M5 16.15v.81L11.997 21l6.998-4.042v-.81M12.003 3 5.005 7.042l6.998 4.042L19 7.042 12.003 3Z"
        ></path>
      </svg>
      <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.page_content.title }}</span>
    </div>
    <div class="p-4">bbb</div>
  </div>
</template>

<style scoped></style>

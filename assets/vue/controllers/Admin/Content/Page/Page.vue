<script lang="ts">
import { defineComponent, PropType } from 'vue';
import SkeletonPageContent from '@/vue/Components/Skeleton/Page/PageContent.vue';
import SkeletonPageTag from '@/vue/Components/Skeleton/Page/PageTag.vue';
import SkeletonPageHistory from '@/vue/Components/Skeleton/Page/PageHistory.vue';
import SkeletonPageSave from '@/vue/Components/Skeleton/Page/PageSave.vue';
import SkeletonPageSEO from '@/vue/Components/Skeleton/Page/PageSEO.vue';
import { PageTranslations } from '@/ts/Page/type';

export default defineComponent({
  name: 'Page',
  components: { SkeletonPageSEO, SkeletonPageSave, SkeletonPageHistory, SkeletonPageTag, SkeletonPageContent },
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
  },
});
</script>

<template>
  <div class="mb-4 mt-4 border-b border-gray-200 dark:border-gray-700" id="nav-tab-option-system">
    <ul
      class="flex items-center flex-wrap mb-px text-sm font-medium text-center"
      id="default-styled-tab"
      data-tabs-toggle="#nav-tab-page"
      data-tabs-active-classes="text-[var(--primary)] hover:text-[var(--primary-hover)] border-[var(--primary)] bg-[var(--primary-lighter)]"
      data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
      role="tablist"
    >
      <li class="me-2" role="presentation">
        <button
          class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
          id="nav-0-tab"
          data-tabs-target="#page-content"
          type="button"
          role="tab"
          :aria-controls="translate.onglet_content"
          aria-selected="true"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 10h16M4 14h16M4 18h16"
            ></path>
          </svg>
          {{ translate.onglet_content }}
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
          id="nav-1-tab"
          data-tabs-target="#page-seo"
          type="button"
          role="tab"
          :aria-controls="translate.onglet_seo"
          aria-selected="false"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            ></path>
          </svg>
          {{ translate.onglet_seo }}
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
          id="nav-2-tab"
          data-tabs-target="#page-tag"
          type="button"
          role="tab"
          :aria-controls="translate.onglet_tags"
          aria-selected="false"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
            ></path>
          </svg>
          {{ translate.onglet_tags }}
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
          id="nav-2-tab"
          data-tabs-target="#page-history"
          type="button"
          role="tab"
          :aria-controls="translate.onglet_history"
          aria-selected="false"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          {{ translate.onglet_history }}
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-flex gap-1.5 items-center ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:hover:text-gray-300"
          id="nav-2-tab"
          data-tabs-target="#page-save"
          type="button"
          role="tab"
          :aria-controls="translate.onglet_save"
          aria-selected="false"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
            ></path>
          </svg>
          {{ translate.onglet_save }}
        </button>
      </li>
    </ul>
  </div>

  <div id="nav-tab-page" class="card rounded-lg p-6 sm:p-8">
    <div class="" id="page-content" role="tabpanel" aria-labelledby="profile-tab">Content</div>
    <div class="hidden" id="page-seo" role="tabpanel" aria-labelledby="profile-tab2">Seo</div>
    <div class="hidden" id="page-tag" role="tabpanel" aria-labelledby="profile-tab3">Tag</div>
    <div class="hidden" id="page-history" role="tabpanel" aria-labelledby="profile-tab3">History</div>
    <div class="hidden" id="page-save" role="tabpanel" aria-labelledby="profile-tab3">Save</div>
  </div>

  <SkeletonPageContent />
  ---
  <SkeletonPageSEO />
  ---
  <SkeletonPageTag />
  ---
  <SkeletonPageHistory />
  ---
  <SkeletonPageSave />
</template>

<style scoped></style>

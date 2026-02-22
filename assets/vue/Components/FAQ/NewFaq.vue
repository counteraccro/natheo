<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import SkeletonFaq from '@/vue/Components/Skeleton/Faq.vue';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'NewFaq',
  components: { SkeletonFaq },
  props: {
    urls: { type: Object as PropType<Record<string, string>>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  data() {
    return {
      loading: false,
      faq: {
        title: null,
      },
    };
  },
  mounted(): any {},
  methods: {},
});
</script>

<template>
  <div v-if="loading" class="mt-5">
    <SkeletonFaq :is-new="true" />
  </div>
  <div v-else>
    <div class="card rounded-lg p-6 mb-4 mt-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <h2 class="text-lg font-bold text-[var(--text-primary)]">{{ translate.new_faq }}</h2>
        <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">{{ translate.new_faq_sub_title }}</p>
      </div>

      <div class="form-group">
        <label class="form-label">{{ translate.new_faq_input_title }}</label>
        <input type="text" class="form-input" />
        <div class="form-text">{{ translate.new_faq_help }}</div>
      </div>

      <div class="flex flex-wrap gap-3 pt-4 mt-5 flex-row-reverse">
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.history.back()">
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            ></path>
          </svg>
          {{ translate.new_faq_btn_cancel }}
        </button>
        <button class="btn btn-sm btn-primary" disabled="true">
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ translate.new_faq_btn }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

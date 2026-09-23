<script lang="ts">
/**
 * Gestionnaire des Pages - Onglet Commentaires
 * @author Gourdon Aymeric
 * @version 2.0
 */

import { defineComponent, PropType } from 'vue';
import { Page, PageData, PageTranslations } from '@/ts/Page/Page.type';

export default defineComponent({
  name: 'PageComment',
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    pageDatas: {
      type: Object as PropType<PageData>,
      required: true,
    },
  },
  emits: ['update-comment'],
  computed: {
    isGlobalOpen(): boolean {
      return this.pageDatas.options_commentaire.open === '1';
    },
    isGlobalModerate(): boolean {
      return this.pageDatas.options_commentaire.new_comment === '1';
    },
  },
  methods: {
    /**
     * Mise à jour d'un paramètre de commentaire de la page
     * @param field
     * @param value
     */
    updateComment(field: 'openComment' | 'ruleComment', value: boolean | number) {
      this.$emit('update-comment', { field, value });
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
            />
          </svg>
          {{ translate.page_comment.title }}
        </div>
      </div>
    </div>

    <div class="p-5 flex flex-col gap-3">
      <div class="flex items-start gap-2 p-3 rounded-lg" style="background-color: var(--primary-lighter)">
        <svg
          class="icon-sm shrink-0 mt-0.5"
          style="color: var(--primary)"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <div class="text-xs" style="color: var(--primary)">
          <div class="font-semibold mb-1">{{ translate.page_comment.info }}</div>
          <span v-if="isGlobalOpen">
            {{ translate.page_comment.comment_open }}
            <span v-if="isGlobalModerate">{{ translate.page_comment.comment_moderate }}</span>
          </span>
          <span v-else>{{ translate.page_comment.comment_close }}</span>
        </div>
      </div>

      <div class="form-group">
        <div class="form-switch form-switch-inline">
          <input
            class="switch-input"
            type="checkbox"
            role="switch"
            id="page-open-comment"
            :checked="page.openComment"
            @change="updateComment('openComment', ($event.target as HTMLInputElement).checked)"
          />
          <label class="switch-toggle" for="page-open-comment"></label>
          <label class="form-check-label" for="page-open-comment">{{ translate.page_comment.input_open_comment }}</label>
        </div>
      </div>

      <div class="form-group">
        <label for="page-rule-comment" class="form-label">{{
          translate.page_comment.input_status_comment_label
        }}</label>
        <select
          id="page-rule-comment"
          class="form-input"
          :value="page.ruleComment"
          @change="updateComment('ruleComment', Number(($event.target as HTMLSelectElement).value))"
        >
          <option v-for="(label, key) in pageDatas.list_comments_status" :key="key" :value="Number(key)">
            {{ label }}
          </option>
        </select>
        <div class="form-text">{{ translate.page_comment.input_status_comment_help }}</div>
      </div>
    </div>
  </div>
</template>

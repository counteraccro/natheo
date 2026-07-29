<script lang="ts">
/**
 * Gestionnaire des Pages - onglet Tag
 * @author Gourdon Aymeric
 * @version 2.0
 **/

import { defineComponent, PropType } from 'vue';
import { Locales, Page, Tag, PageTranslations, Urls, TagSuggestion } from '@/ts/Page/type';
import axios from 'axios';

export default defineComponent({
  name: 'PageTag',
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
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
  },
  emits: ['update-tags'],
  data() {
    return {
      inputValue: '' as string,
      suggestions: [] as TagSuggestion[],
      suggestionsVisible: false as boolean,
      searchTimeout: null as ReturnType<typeof setTimeout> | null,
      isSearching: false as boolean,
    };
  },
  computed: {
    currentTags(): Tag[] {
      return this.page.tags ?? [];
    },
  },
  methods: {
    getTagLabel(tag: Tag): string {
      return (
        tag.tagTranslations.find((t) => t.locale === this.currentLocale)?.label ?? tag.tagTranslations[0]?.label ?? ''
      );
    },
    onInput() {
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }

      if (this.inputValue.trim() === '') {
        this.suggestions = [];
        this.suggestionsVisible = false;
        return;
      }

      this.searchTimeout = setTimeout(() => {
        this.searchTags();
      }, 300);
    },
    searchTags() {
      this.isSearching = true;
      axios
        .get(this.urls.auto_complete_tag + '/' + this.inputValue.trim() + '/' + this.currentLocale)
        .then((response) => {
          const result = response.data.result as Record<string, TagSuggestion>;
          this.suggestions = Object.values(result).filter((tag) => !this.currentTags.some((t) => t.id === tag.id));
          this.suggestionsVisible = this.suggestions.length > 0;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.isSearching = false;
        });
    },
    selectSuggestion(suggestion: TagSuggestion) {
      this.inputValue = suggestion.label;
      this.suggestionsVisible = false;
      this.fetchOrCreateTag();
    },
    onEnter() {
      if (this.inputValue.trim() === '') {
        return;
      }
      this.fetchOrCreateTag();
    },
    fetchOrCreateTag() {
      axios
        .get(this.urls.tag_by_name + '/' + this.inputValue.trim() + '/' + this.currentLocale, {})
        .then((response) => {
          if (response.data.success) {
            const tag: Tag = response.data.tag;
            if (!this.currentTags.some((t) => t.id === tag.id)) {
              this.addTag(tag);
            }
          }
        })
        .catch((error) => {
          console.error(error);
        });
    },
    addTag(tag: Tag) {
      this.$emit('update-tags', [...this.currentTags, tag]);
      this.inputValue = '';
      this.suggestions = [];
      this.suggestionsVisible = false;
    },
    removeTag(tag: Tag) {
      this.$emit(
        'update-tags',
        this.currentTags.filter((t) => t.id !== tag.id)
      );
    },
    hideSuggestions() {
      setTimeout(() => {
        this.suggestionsVisible = false;
      }, 200);
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
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
            />
          </svg>
          {{ translate.tag_title }}
        </div>
        <p class="card-subtitle">{{ translate.tag_sub_title }}</p>
      </div>
    </div>
    <div class="p-5">
      <div class="form-group">
        <label for="tagInputField" class="form-label">{{ translate.auto_complete.auto_complete_label }}</label>
        <div class="relative">
          <div class="input-tags" id="tagInput">
            <div v-for="tag in currentTags" :key="tag.id" class="tag-item">
              <span class="w-2 h-2 rounded-full shrink-0" :style="'background-color: ' + tag.color"></span>
              {{ getTagLabel(tag) }}
              <button type="button" @click="removeTag(tag)">
                <svg style="width: 0.625rem; height: 0.625rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <input
              type="text"
              id="tagInputField"
              v-model="inputValue"
              :placeholder="translate.auto_complete.auto_complete_placeholder"
              @input="onInput"
              @keydown.enter.prevent="onEnter"
              @blur="hideSuggestions"
            />
          </div>

          <div
            v-if="suggestionsVisible"
            class="absolute left-0 right-0 z-10 rounded-lg shadow-lg border mt-1"
            style="background-color: var(--bg-card); border-color: var(--border-color)"
          >
            <ul class="py-1">
              <li
                v-for="tag in suggestions"
                :key="tag.id"
                class="flex items-center gap-2 px-4 py-2 cursor-pointer text-sm"
                style="color: var(--text-primary)"
                @mousedown="selectSuggestion(tag)"
                @mouseenter="($event.currentTarget as HTMLElement).style.backgroundColor = 'var(--bg-hover)'"
                @mouseleave="($event.currentTarget as HTMLElement).style.backgroundColor = ''"
              >
                <span class="w-2 h-2 rounded-full shrink-0" :style="'background-color: ' + tag.color"></span>
                {{ tag.label }}
              </li>
            </ul>
          </div>

          <div v-if="isSearching" class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg
              class="w-4 h-4 animate-spin"
              style="color: var(--text-secondary)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
          </div>
        </div>
        <span class="form-text">{{ translate.auto_complete.auto_complete_help }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

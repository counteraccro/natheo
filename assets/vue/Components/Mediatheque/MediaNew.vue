<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Edition d'un média ou dossier
 */

import { defineComponent, type PropType } from 'vue';
import { FileData, TranslateRecord } from '@/ts/Mediatheque/type';
import FileUpload from '@/vue/Components/Global/FileUpload.vue';

export default defineComponent({
  name: 'MediaNew',
  components: { FileUpload },
  props: {
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  emits: ['close'],
  data() {
    return {
      copied: false,
    };
  },
  computed: {},
  methods: {
    saveMedia(file: FileData) {
      console.log(file);
    },
  },
});
</script>

<template>
  <div class="info-drawer-inner">
    <!-- Header du panneau -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="flex gap-1 items-center font-semibold text-sm" style="color: var(--text-primary)">
        <svg
          class="w-4 h-4 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          style="color: var(--primary)"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
          ></path>
        </svg>
        {{ translate.title }}
      </h3>
      <button
        @click="$emit('close')"
        class="p-1.5 rounded-lg transition"
        style="color: var(--text-secondary)"
        onmouseover="this.style.backgroundColor = 'var(--bg-hover)'"
        onmouseout="this.style.backgroundColor = ''"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <file-upload :max-size="20" :translate="translate" @file-uploaded="saveMedia" />
  </div>
</template>

<style scoped></style>

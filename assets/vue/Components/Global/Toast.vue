<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Affichage d'un toast (succès ou erreur), piloté par ToastContainer
 */

import { defineComponent, type PropType } from 'vue';
import type { ToastType } from '@/ts/Toast/Toast.type';
import { uiTrans } from '@/vue/Composables/Global/uiTranslate';

const CSS: Record<ToastType, { bg: string; text: string; border: string }> = {
  success: {
    bg: 'bg-[var(--alert-success-bg)]',
    text: 'text-[var(--alert-success-text)]',
    border: 'border-[var(--alert-success-border)]',
  },
  danger: {
    bg: 'bg-[var(--alert-danger-bg)]',
    text: 'text-[var(--alert-danger-text)]',
    border: 'border-[var(--alert-danger-border)]',
  },
};

export default defineComponent({
  name: 'Toast',
  props: {
    type: { type: String as PropType<ToastType>, default: 'success' },
    msg: { type: String, required: true },
  },
  emits: ['close'],
  computed: {
    cssElement(): { bg: string; text: string; border: string } {
      return CSS[this.type];
    },
  },
  methods: {
    uiTrans,
  },
});
</script>

<template>
  <div
    class="flex items-center w-full max-w-xs p-4 text-sm space-x-4 text-gray-500 bg-white border divide-x rtl:divide-x-reverse divide-gray-200 rounded-lg shadow-sm dark:text-gray-400 dark:divide-gray-700 dark:bg-gray-800"
    :class="cssElement.border"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <div
      v-if="type === 'success'"
      class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg"
      :class="cssElement.bg + ' ' + cssElement.text"
    >
      <svg
        class="w-5 h-5"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"
        />
      </svg>
    </div>
    <div
      v-else-if="type === 'danger'"
      class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg"
      :class="cssElement.bg + ' ' + cssElement.text"
    >
      <svg
        class="w-5 h-5"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"
        />
      </svg>
    </div>
    <div class="ms-3 text-xs font-normal" :class="cssElement.text">
      <div v-html="msg"></div>
    </div>
    <button
      type="button"
      class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
      @click="$emit('close')"
      :aria-label="uiTrans('close')"
    >
      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
        />
      </svg>
    </button>
  </div>
</template>

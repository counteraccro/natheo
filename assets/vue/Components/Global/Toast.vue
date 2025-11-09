<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Toast Bootstrap 5.3
 */

import { watch, watchEffect } from 'vue';

export default {
  name: 'Toast',
  props: {
    id: String,
    show: Boolean,
    type: {
      type: String,
      default: 'success',
    },
  },
  emits: ['close-toast'],
  data() {
    return {
      elm: {},
      cssElement: {
        bg: 'bg-[var(--alert-success-bg)]',
        text: 'text-[var(--alert-success-text)]',
        border: 'border-[var(--alert-success-border)]',
      },
    };
  },
  mounted() {
    this.elm = document.getElementById(this.id);
    this.elm.classList.add('hidden');

    this.elm.addEventListener('hide.bs.toast', () => {
      this.$emit('close-toast', this.id);
    });

    switch (this.type) {
      case 'success':
        this.cssElement.bg = 'bg-[var(--alert-success-bg)]';
        this.cssElement.text = 'text-[var(--alert-success-text)]';
        this.cssElement.border = 'border-[var(--alert-success-border)]';
        break;
      case 'danger':
        this.cssElement.bg = 'bg-[var(--alert-danger-bg)]';
        this.cssElement.text = 'text-[var(--alert-danger-text)]';
        this.cssElement.border = 'border-[var(--alert-danger-border)]';
        break;
    }

    watch(
      () => this.show,
      (newValue, oldValue) => {
        if (newValue !== oldValue) {
          if (newValue) {
            this.elm.classList.remove('hidden');
            setTimeout(() => {
              this.elm.classList.add('hidden');
              this.$emit('close-toast', this.id);
            }, 3000);
          } else {
            this.elm.classList.add('hidden');
          }
        }
      },
      { immediate: true, deep: true }
    );
  },
  methods: {
    close() {
      this.elm.classList.add('hidden');
      this.$emit('close-toast', this.id);
    },
  },
};
</script>

<template>
  <div
    :id="this.id"
    class="fixed hidden flex items-center w-full max-w-xs p-4 text-sm space-x-4 text-gray-500 bg-white border divide-x rtl:divide-x-reverse divide-gray-200 rounded-lg shadow-sm top-5 right-5 dark:text-gray-400 dark:divide-gray-700 dark:bg-gray-800"
    :class="this.cssElement.border"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <div
      v-if="this.type === 'success'"
      class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg"
      :class="this.cssElement.bg + ' ' + this.cssElement.text"
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
      <span class="sr-only">Check icon</span>
    </div>
    <div
      v-else-if="type === 'danger'"
      class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg"
      :class="this.cssElement.bg + ' ' + this.cssElement.text"
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
      <span class="sr-only">Error icon</span>
    </div>
    <div class="ms-3 text-xs font-normal" :class="this.cssElement.text">
      <slot name="body"></slot>
    </div>
    <button
      type="button"
      class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
      @click="this.close"
      aria-label="Close"
    >
      <span class="sr-only">Close</span>
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

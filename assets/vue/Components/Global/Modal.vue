<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modale Bootstrap 5.3
 */

import { watch } from 'vue';

export default {
  name: 'Modal',
  props: {
    id: String,
    show: Boolean,
    optionShowCloseBtn: {
      type: Boolean,
      default: true,
    },
    optionModalSize: {
      type: String,
      default: '',
    },
    optionModalBackdrop: {
      type: String,
      default: null,
    },
  },
  emits: ['close-modal'],
  data() {
    return {
      modal: {},
    };
  },
  mounted() {
    const modalElement = document.getElementById(this.id);
    const modalOptions = {
      placement: 'center',
      backdrop: 'dynamic',
      backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
      closable: false,
      onHide: () => {},
      onShow: () => {},
      onToggle: () => {},
    };
    this.modal = new Modal(modalElement, modalOptions);

    watch(
      () => this.show,
      (newValue, oldValue) => {
        if (newValue !== oldValue) {
          if (newValue) {
            this.modal.show();
          } else {
            this.modal.hide();
          }
        }
      },
      { immediate: true, deep: true }
    );
  },
  methods: {
    close() {
      this.modal.hide();
      this.$emit('close-modal', this.id);
    },
  },
};
</script>

<template>
  <div
    :id="this.id"
    :data-modal-backdrop="this.optionModalBackdrop"
    tabindex="-1"
    aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
  >
    <div class="relative p-4 w-full max-w-2xl max-h-full">
      <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
        <div
          class="flex p-4 md:p-5 rounded-t border-b text-white dark:border-b-[var(--primary)]"
          style="background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%)"
        >
          <slot name="icon"></slot>

          <h3 class="text-xl font-semibold">
            <slot name="title"></slot>
          </h3>
          <button
            v-if="this.optionShowCloseBtn"
            type="button"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
            :data-modal-hide="this.id"
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
            <span class="sr-only">Close modal</span>
          </button>
        </div>
        <!-- Modal body -->
        <div class="p-4 md:p-5 space-y-4">
          <slot name="body"></slot>
        </div>
        <!-- Modal footer -->
        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <slot name="footer"></slot>
          <button v-if="this.optionShowCloseBtn" type="button" class="btn btn-secondary" @click="this.close()">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

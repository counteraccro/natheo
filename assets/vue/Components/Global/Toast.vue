<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Toast Bootstrap 5.3
 */

import { watch } from 'vue';
import { Toast } from 'bootstrap';

export default {
  name: 'Toast',
  props: {
    id: String,
    show: Boolean,
    optionClassHeader: {
      type: String,
      default: '',
    },
  },
  emits: ['close-toast'],
  data() {
    return {
      toast: {},
    };
  },
  mounted() {
    let elm = document.getElementById(this.id);
    this.toast = Toast.getOrCreateInstance(elm);

    elm.addEventListener('hide.bs.toast', () => {
      this.$emit('close-toast', this.id);
    });

    watch(
      () => this.show,
      (newValue, oldValue) => {
        if (newValue !== oldValue) {
          if (newValue) {
            this.toast.show();
          } else {
            this.toast.hide();
          }
        }
      },
      { immediate: true, deep: true }
    );
  },
  methods: {
    close() {
      this.toast.hide();
      this.$emit('close-toast', this.id);
    },
  },
};
</script>

<template>
  <div
    :id="this.id"
    class="toast border border-secondary bg-white"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <div class="toast-header" :class="this.optionClassHeader">
      <slot name="header"></slot>
      <button type="button" class="btn-close" @click="this.close"></button>
    </div>
    <div class="toast-body">
      <slot name="body"></slot>
    </div>
  </div>
</template>

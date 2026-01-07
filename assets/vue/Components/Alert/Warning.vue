<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Warning
 */
export default {
  name: 'AlertWarning',
  props: {
    type: {
      type: String,
      default: 'alert-warning-solid',
    },
    text: String,
  },
  emits: [],
  data() {
    return {};
  },
  mounted() {},

  methods: {},
};
</script>

<template>
  <div class="alert" :class="this.type">
    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
      ></path>
    </svg>
    <div class="alert-content">
      <div class="alert-message" v-html="this.text"></div>
    </div>
  </div>
</template>

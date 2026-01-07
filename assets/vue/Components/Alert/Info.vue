<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Info
 */
export default {
  name: 'AlertInfo',
  props: {
    type: {
      type: String,
      default: 'alert-info-solid',
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
        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
      ></path>
    </svg>
    <div class="alert-content">
      <div class="alert-message" v-html="this.text"></div>
    </div>
  </div>
</template>

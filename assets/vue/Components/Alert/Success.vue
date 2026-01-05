<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Success
 */
export default {
  name: 'AlertSuccess',
  props: {
    type: {
      type: String,
      default: 'alert-success-solid',
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
    <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
      <path
        fill-rule="evenodd"
        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"
      ></path>
    </svg>
    <div class="alert-content">
      <div class="alert-message" v-html="this.text"></div>
    </div>
  </div>
</template>

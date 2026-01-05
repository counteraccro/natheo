<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Danger
 */
export default {
  name: 'AlertDanger',
  props: {
    type: {
      type: String,
      default: 'alert-danger-solid',
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
        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
      ></path>
    </svg>
    <div class="alert-content">
      <div class="alert-message" v-html="this.text"></div>
    </div>
  </div>
</template>

<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher les flashs messages sous la forme d'un toast
 */
import toast from 'bootstrap/js/src/toast';

export default {
  name: 'Flash',
  props: {
    flashes: Object,
    translate: Object,
  },
  mounted() {
    this.flashes.forEach((row) => {
      this.showToast('toast-flash-' + row.label);
    });
  },
  methods: {
    showToast(id) {
      let element = document.getElementById(id);
      const toastFlash = toast.getOrCreateInstance(element);
      toastFlash.show();
    },
  },
};
</script>

<template>
  <div class="toast-container top-0 end-0 p-3">
    <div v-for="{ label, message } in this.flashes">
      <div
        :id="'toast-flash-' + label"
        class="toast border border-secondary bg-white"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="toast-header" :class="'text-' + label">
          <i v-if="label === 'success'" class="bi bi-check-circle-fill"></i>
          <strong class="me-auto" v-if="label === 'success'">&nbsp; {{ this.translate.title_success }}</strong>
          <i v-if="label === 'warning'" class="bi bi-question-circle-fill"></i>
          <strong class="me-auto" v-if="label === 'warning'">&nbsp; {{ this.translate.title_warning }}</strong>
          <i v-if="label === 'danger'" class="bi bi-exclamation-triangle-fill"></i>
          <strong class="me-auto" v-if="label === 'danger'"> &nbsp;{{ this.translate.title_danger }}</strong>
          <small class="text-black-50">{{ translate.time }}</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <span v-html="' ' + message"></span>
        </div>
      </div>
    </div>
  </div>
</template>

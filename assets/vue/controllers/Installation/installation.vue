<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import Toast from "../../Components/Global/Toast.vue";

export default {
  name: "Installation-step-one",
  components: {
    Toast

  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
  },
  data() {
    return {
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      },
    }
  },
  mounted() {

  },
  methods: {

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },
  },
}
</script>

<template>

  <div class="col-lg-8 mx-auto p-4 py-md-5">


    <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
      <i class="bi bi-database-fill-slash h3 me-2"></i>
      <span class="fs-4"> {{ this.translate.title }}</span>
    </header>

    <main>
      <h1 class="text-body-emphasis">{{ this.translate.title_h1 }}</h1>

    </main>
  </div>


  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastError'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastError.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>

</template>
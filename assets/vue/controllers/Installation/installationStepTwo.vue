<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";

export default {
  name: "Installation-step-two",
  components: {
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    datas: Object
  },
  data() {
    return {
      loading: false,
    }
  },
  mounted() {

  },

  computed: {},

  methods: {

  },
}
</script>

<template>

  <div id="installation-step-one" class="col-lg-8 mx-auto p-4 py-md-5" :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="d-flex align-items-center pb-3 mb-3 border-bottom">
      <i class="bi bi-box-seam h3 me-2"></i>
      <span class="fs-4"> {{ this.translate.title }}</span>
    </div>

    <h1 class="text-body-emphasis">{{ this.translate.title_h1 }}</h1>
    <p>
      bla bla
    </p>
  </div>
</template>
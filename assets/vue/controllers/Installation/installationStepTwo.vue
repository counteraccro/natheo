<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";

export default {
  name: "Installation-step-two",
  components: {},
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

  methods: {},
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
      {{ this.translate.fondateur_description }}
    </p>

    <div class="card">
      <div class="card-header">
        <i class="bi bi-person-add"></i> {{ this.translate.fondateur_titre_card }}
      </div>
      <div class="card-body">
        <div class="row">

          <div class="col-12">
            <div class="mb-3">
              <label for="user-email" class="form-label">{{ this.translate.fondateur_email_label }}</label>
              <div class="input-group">
                <span class="input-group-text" id="addon-wrapping"><i class="bi bi-envelope"></i> </span>
                <input type="email" class="form-control is-invalid" id="user-email" :placeholder="this.translate.fondateur_email_placeholder">
                <div id="invalide-user-email" class="invalid-feedback">
                  {{ this.translate.fondateur_email_error }}
                </div>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="mb-3">
              <label for="user-login" class="form-label">{{ this.translate.fondateur_login_label }}</label>
              <div class="input-group">
                <span class="input-group-text" id="addon-wrapping"><i class="bi bi-person"></i> </span>
                <input type="text" class="form-control is-invalid" id="user-login" :placeholder="this.translate.fondateur_login_placeholder">
                <div id="invalide-user-login" class="invalid-feedback">
                  {{ this.translate.fondateur_login_error }}
                </div>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="mb-3">
              <label for="user-password" class="form-label">{{ this.translate.fondateur_password_label }}</label>
              <div class="input-group">
                <span class="input-group-text" id="addon-wrapping"><i class="bi bi-eye-slash"></i> </span>
                <input type="password" class="form-control is-invalid" id="user-password" autocomplete="current-password">
                <div class="input-group-text">
                  <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input">
                </div>
                <div id="invalide-user-login" class="invalid-feedback">
                  {{ this.translate.fondateur_password_error }}
                </div>
                <div class="form-text">
                  <span>{{ this.translate.fondateur_password_help }}</span> <br />
                  <span class="text-danger"><i class="bi bi-x-circle"></i> {{ this.translate.fondateur_password_weak }}</span> <br />
                  <span class="text-warning"><i class="bi bi-exclamation-circle"></i> {{ this.translate.fondateur_password_normal }}</span> <br />
                  <span class="text-success"><i class="bi bi-check-circle"></i> {{ this.translate.fondateur_password_strong }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="btn btn-secondary float-end">{{ this.translate.fondateur_btn_create }}</div>
      </div>
    </div>

  </div>
</template>
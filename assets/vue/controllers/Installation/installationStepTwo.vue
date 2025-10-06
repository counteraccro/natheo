<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from 'axios';

export default {
  name: 'Installation-step-two',
  components: {},
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    datas: Object,
  },
  data() {
    return {
      loading: false,
      userSuccess: false,
      userLoading: false,
      user: {
        login: null,
        email: null,
        password: null,
      },
      userValidate: {
        login: null,
        email: null,
        password: null,
        showPasswordRule: {
          icon: 'bi-eye-slash',
          type: 'password',
          showPassword: false,
        },
        passwordRule: {
          weak: false,
          normal: false,
          strong: false,
        },
      },
      finishLoading: {
        loading: false,
        loadConfig: 0,
        installData: 0,
        clearCache: 0,
        redirect: false,
        error: null,
      },
    };
  },
  mounted() {},

  computed: {},

  methods: {
    /**
     * Vérifie la validité de l'adresse email
     */
    validateEmail() {
      this.userValidate.email = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.user.email);
      this.canCreateUser();
    },

    /**
     * Met la class is-valid ou is-invalid en fonction de userValidate
     * @param field
     * @return {string}
     */
    isValidate(field) {
      if (this.userValidate[field] === null) {
        return '';
      }

      if (this.userValidate[field] === true) {
        return 'is-valid';
      } else {
        return 'is-invalid';
      }
    },

    /**
     * Sécurise le login
     */
    sanitizeLogin(event) {
      this.user.login = event.target.value.replace(/[^\w\s]/gi, '');
    },

    /**
     * Validation du login
     */
    validateLogin() {
      this.userValidate.login = this.user.login !== '';
      this.canCreateUser();
    },

    /**
     * Valide le mot de passe
     */
    validatePassword() {
      this.userValidate.passwordRule.strong = false;
      this.userValidate.passwordRule.weak = false;
      this.userValidate.passwordRule.normal = false;

      var strongRegex = new RegExp('^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})');
      var mediumRegex = new RegExp(
        '^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})'
      );

      if (strongRegex.test(this.user.password)) {
        this.userValidate.passwordRule.strong = true;
        this.userValidate.password = true;
      } else if (mediumRegex.test(this.user.password)) {
        this.userValidate.passwordRule.normal = true;
        this.userValidate.password = false;
      } else {
        this.userValidate.passwordRule.weak = true;
        this.userValidate.password = false;
      }

      this.canCreateUser();
    },

    /**
     * Affichage ou masque le mot de passe
     */
    showPassword() {
      if (this.userValidate.showPasswordRule.showPassword) {
        this.userValidate.showPasswordRule.icon = 'bi-eye';
        this.userValidate.showPasswordRule.type = 'text';
      } else {
        this.userValidate.showPasswordRule.icon = 'bi-eye-slash';
        this.userValidate.showPasswordRule.type = 'password';
      }
    },

    /**
     * Désactive ou active le bouton submit pour créer le user
     * @return {string}
     */
    canCreateUser() {
      if (this.userValidate.password && this.userValidate.login && this.userValidate.email) {
        return '';
      }
      return 'disabled';
    },

    /**
     * Check si l'input doit etre disabled ou non
     * @return {boolean}
     */
    isDisabled() {
      return this.userSuccess;
    },

    /**
     * Création de l'utilisateur
     */
    createUser() {
      this.userLoading = true;
      axios
        .post(this.urls.create_user, {
          user: this.user,
        })
        .then((response) => {
          this.userSuccess = response.data.success;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.userLoading = false;
        });
    },

    /**
     * Installation finale
     */
    finishInstallation() {
      this.finishLoading.loadConfig = 0;
      this.finishLoading.installData = 0;
      this.finishLoading.clearCache = 0;
      this.finishLoading.error = null;
      this.finishLoading.redirect = false;
      this.finishLoading.loading = true;

      this.switchEnv();
    },

    /**
     * Change l'environnement
     */
    switchEnv() {
      this.finishLoading.loadConfig = 1;
      axios
        .get(this.urls.change_env, {})
        .then((response) => {
          if (response.data.success) {
            this.finishLoading.loadConfig = 2;
            this.loadFixture();
          } else {
            this.finishLoading.loadConfig = 3;
            this.finishLoading.error = response.data.error;
            this.finishLoading.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {});
    },

    /**
     * Charge les données d'installation
     */
    loadFixture() {
      this.finishLoading.installData = 1;
      axios
        .get(this.urls.load_fixtures, {})
        .then((response) => {
          if (response.data.success) {
            this.finishLoading.installData = 2;
            this.clearCache();
          } else {
            this.finishLoading.installData = 3;
            this.finishLoading.error = response.data.error;
            this.finishLoading.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {});
    },

    /**
     * Nettoyage du cache
     */
    clearCache() {
      this.finishLoading.clearCache = 1;
      axios
        .get(this.urls.clear_cache, {})
        .then((response) => {
          if (response.data.success) {
            this.finishLoading.clearCache = 2;
            this.finishLoading.redirect = true;
          } else {
            this.finishLoading.clearCache = 3;
            this.finishLoading.error = response.data.error;
            this.finishLoading.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          document.location.href = this.urls.auth;
        });
    },
  },
};
</script>

<template>
  <div
    id="installation-step-one"
    class="col-lg-8 mx-auto p-4 py-md-5"
    :class="this.loading === true ? 'block-grid' : ''"
  >
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
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

    <div v-if="this.datas.debug_mode" class="alert alert-danger">
      <h5 class="alert-heading"><i class="bi bi-bug"></i> {{ this.translate.debug_titre }}</h5>
      <p>{{ this.translate.debug_texte_1 }}</p>
      <p>{{ this.translate.debug_texte_2 }}</p>
      <p>
        {{ this.translate.debug_texte_3 }}
      </p>
      <ul>
        <li v-html="this.translate.debug_texte_4"></li>
        <li v-html="this.translate.debug_texte_5"></li>
        <li v-html="this.translate.debug_texte_6"></li>
      </ul>
    </div>

    <div class="card">
      <div class="card-header"><i class="bi bi-person-add"></i> {{ this.translate.fondateur_titre_card }}</div>
      <div class="card-body">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="user-email" class="form-label">{{ this.translate.fondateur_email_label }}</label>
              <div class="input-group">
                <span class="input-group-text" id="addon-wrapping"><i class="bi bi-envelope"></i> </span>
                <input
                  type="email"
                  class="form-control"
                  :class="this.isValidate('email')"
                  v-model="this.user.email"
                  id="user-email"
                  :placeholder="this.translate.fondateur_email_placeholder"
                  :disabled="this.isDisabled()"
                  @change="this.validateEmail()"
                />
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
                <input
                  type="text"
                  class="form-control"
                  :class="this.isValidate('login')"
                  v-model="this.user.login"
                  id="user-login"
                  :placeholder="this.translate.fondateur_login_placeholder"
                  :disabled="this.isDisabled()"
                  @keyup="this.sanitizeLogin"
                  @change="this.validateLogin()"
                />
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
                <span class="input-group-text" id="addon-wrapping"
                  ><i class="bi" :class="this.userValidate.showPasswordRule.icon"></i>
                </span>
                <input
                  :type="this.userValidate.showPasswordRule.type"
                  maxlength="20"
                  :disabled="this.isDisabled()"
                  class="form-control"
                  :class="this.isValidate('password')"
                  v-model="this.user.password"
                  id="user-password"
                  @keyup="this.validatePassword()"
                />
                <div class="input-group-text">
                  <input
                    class="form-check-input mt-0"
                    type="checkbox"
                    :disabled="this.isDisabled()"
                    v-model="this.userValidate.showPasswordRule.showPassword"
                    @change="this.showPassword()"
                  />
                </div>
                <div id="invalide-user-login" class="invalid-feedback">
                  {{ this.translate.fondateur_password_error }}
                </div>
                <div class="form-text">
                  <span>{{ this.translate.fondateur_password_help }}</span> <br />
                  <span v-if="this.userValidate.passwordRule.weak" class="text-danger"
                    ><i class="bi bi-x-circle"></i> {{ this.translate.fondateur_password_weak }}</span
                  >
                  <span v-if="this.userValidate.passwordRule.normal" class="text-warning"
                    ><i class="bi bi-exclamation-circle"></i> {{ this.translate.fondateur_password_normal }}</span
                  >
                  <span v-if="this.userValidate.passwordRule.strong" class="text-success"
                    ><i class="bi bi-check-circle"></i> {{ this.translate.fondateur_password_strong }}</span
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div
          v-if="!this.userLoading && !this.userSuccess"
          class="btn btn-secondary float-end"
          :class="this.canCreateUser()"
          @click="this.createUser()"
        >
          <i class="bi bi-person-add"></i> {{ this.translate.fondateur_btn_create }}
        </div>

        <div v-if="this.userLoading" class="btn btn-secondary float-end disabled">
          <div>
            <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
            <i>&nbsp;{{ this.translate.fondateur_loading_msg }}</i>
          </div>
        </div>

        <div v-if="this.userSuccess" class="btn btn-secondary float-end disabled">
          <i class="bi bi-person-check"></i> {{ this.translate.fondateur_success }}
        </div>
      </div>
    </div>

    <div v-if="this.userSuccess" class="card mt-4">
      <div class="card-header">
        <i class="bi bi-box-arrow-in-down-right"></i> {{ this.translate.finish_installation_title }}
      </div>
      <div class="card-body">
        <p>{{ this.translate.finish_installation_text_1 }}</p>
        <p>{{ this.translate.finish_installation_text_2 }}</p>
      </div>
      <div class="card-footer">
        <div
          class="btn btn-secondary float-end"
          :class="this.finishLoading.loading ? 'disabled' : ''"
          @click="this.finishInstallation()"
        >
          <i class="bi bi-box-seam"></i> {{ this.translate.finish_installation_btn }}
        </div>

        <div v-if="this.finishLoading.loadConfig === 1">
          <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
          <i>&nbsp;{{ this.translate.finish_installation_loading_msg_config }}</i>
        </div>
        <div v-else-if="this.finishLoading.loadConfig === 2">
          <span class="text-success"
            ><i class="bi bi-check-circle-fill"> </i>
            {{ this.translate.finish_installation_loading_msg_config_success }}</span
          >
        </div>
        <div v-else-if="this.finishLoading.loadConfig === 3">
          <span class="text-danger"
            ><i class="bi bi-x-circle-fill"> </i> {{ this.translate.finish_installation_loading_msg_config_ko }}</span
          >
        </div>

        <div v-if="this.finishLoading.installData === 1">
          <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
          <i>&nbsp;{{ this.translate.finish_installation_loading_msg_installation_data }}</i>
        </div>
        <div v-else-if="this.finishLoading.installData === 2">
          <span class="text-success"
            ><i class="bi bi-check-circle-fill"> </i>
            {{ this.translate.finish_installation_loading_msg_installation_data_success }}</span
          >
        </div>
        <div v-else-if="this.finishLoading.installData === 3">
          <span class="text-danger"
            ><i class="bi bi-x-circle-fill"> </i>
            {{ this.translate.finish_installation_loading_msg_installation_data_ko }}</span
          >
        </div>

        <div v-if="this.finishLoading.clearCache === 1">
          <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
          <i>&nbsp;{{ this.translate.finish_installation_loading_msg_cache }}</i>
        </div>
        <div v-else-if="this.finishLoading.clearCache === 2">
          <span class="text-success"
            ><i class="bi bi-check-circle-fill"> </i>
            {{ this.translate.finish_installation_loading_msg_cache_success }}</span
          >
        </div>
        <div v-else-if="this.finishLoading.clearCache === 3">
          <span class="text-danger"
            ><i class="bi bi-x-circle-fill"> </i> {{ this.translate.finish_installation_loading_msg_cache_ko }}</span
          >
        </div>

        <div v-if="this.finishLoading.redirect">
          <span class="text-success"
            ><i class="bi bi-check-circle-fill"> </i> {{ this.translate.finish_installation_loading_msg_success }}</span
          >
        </div>

        <div v-if="this.finishLoading.error !== null">
          <span class="text-danger"
            >&emsp;<i class="bi bi-arrow-return-right"> </i> {{ this.finishLoading.error }}</span
          >
        </div>
      </div>
    </div>
  </div>
</template>

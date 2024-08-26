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
          showPassword: false
        },
        passwordRule: {
          weak: false,
          normal: false,
          strong: false,
        },
      }
    }
  },
  mounted() {

  },

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
        return "";
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

      var strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
      var mediumRegex = new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})");

      if (strongRegex.test(this.user.password)) {
        this.userValidate.passwordRule.strong = true;
        this.userValidate.password = true
      } else if (mediumRegex.test(this.user.password)) {
        this.userValidate.passwordRule.normal = true;
        this.userValidate.password = false
      } else {
        this.userValidate.passwordRule.weak = true;
        this.userValidate.password = false
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
    canCreateUser()
    {
      if(this.userValidate.password && this.userValidate.login && this.userValidate.email) {
        console.log('oki');
        return "";
      }

      console.log('nop');
      return "disabled";
    },

    /**
     * Création de l'utilisateur
     */
    createUser()
    {
      console.log('crée');
    }
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
                <input type="email" class="form-control" :class="this.isValidate('email')" v-model="this.user.email"
                    id="user-email" :placeholder="this.translate.fondateur_email_placeholder" @change="this.validateEmail()">
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
                <input type="text" class="form-control" :class="this.isValidate('login')" v-model="this.user.login"
                    id="user-login" :placeholder="this.translate.fondateur_login_placeholder" @keyup="this.sanitizeLogin" @change="this.validateLogin()">
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
                <span class="input-group-text" id="addon-wrapping"><i class="bi" :class="this.userValidate.showPasswordRule.icon"></i> </span>
                <input :type="this.userValidate.showPasswordRule.type" maxlength="20" class="form-control" :class="this.isValidate('password')" v-model="this.user.password" id="user-password" @keyup="this.validatePassword()">
                <div class="input-group-text">
                  <input class="form-check-input mt-0" type="checkbox" v-model="this.userValidate.showPasswordRule.showPassword" @change="this.showPassword()">
                </div>
                <div id="invalide-user-login" class="invalid-feedback">
                  {{ this.translate.fondateur_password_error }}
                </div>
                <div class="form-text">
                  <span>{{ this.translate.fondateur_password_help }}</span> <br/>
                  <span v-if="this.userValidate.passwordRule.weak" class="text-danger"><i class="bi bi-x-circle"></i> {{ this.translate.fondateur_password_weak }}</span>
                  <span v-if="this.userValidate.passwordRule.normal" class="text-warning"><i class="bi bi-exclamation-circle"></i> {{ this.translate.fondateur_password_normal }}</span>
                  <span v-if="this.userValidate.passwordRule.strong" class="text-success"><i class="bi bi-check-circle"></i> {{ this.translate.fondateur_password_strong }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="btn btn-secondary float-end" :class="this.canCreateUser()" @click="this.createUser()">{{ this.translate.fondateur_btn_create }}</div>
      </div>
    </div>

  </div>
</template>
<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Changement du mot de passe pour un compte user
 */

import axios from 'axios';

export default {
  name: 'ChangePassword',
  props: {
    url_change_password: String,
    translate: Object,
    fullScreen: {
      default: false,
      type: Boolean,
    },
  },
  data() {
    return {
      password: '',
      classPassword: '',
      passwordConfirm: '',
      classPasswordConfirm: '',
      redirect: false,
      loading: false,
      btnSubmit: true,
      progressColor: 'bg-[var(--btn-danger)]',
      msgUpdatePassword: '',
      nbCharacter: {
        class: 'text-[var(--input-invalid)]',
        icon: 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        progress: 0,
      },
      majuscule: {
        class: 'text-[var(--input-invalid)]',
        icon: 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        progress: 0,
      },
      minuscule: {
        class: 'text-[var(--input-invalid)]',
        icon: 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        progress: 0,
      },
      chiffre: {
        class: 'text-[var(--input-invalid)]',
        icon: 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        progress: 0,
      },
      special: {
        class: 'text-[var(--input-invalid)]',
        icon: 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        progress: 0,
      },
      rule: {
        start: '^',
        end: '$',
        nbCharacter: '.{8,}',
        majuscule: '(?=.*[A-Z])',
        minuscule: '(?=.*?[a-z])',
        chiffre: '(?=.*?[0-9])',
        special: '(?=.*?[#?!@$%^&*-])',
      },
    };
  },
  computed: {
    progress() {
      let nb =
        this.nbCharacter.progress +
        this.majuscule.progress +
        this.minuscule.progress +
        this.chiffre.progress +
        this.special.progress;

      switch (nb) {
        case 20:
        case 40:
          this.progressColor = 'bg-[var(--btn-danger)]';
          break;
        case 60:
        case 80:
          this.progressColor = 'bg-[var(--btn-warning)]';
          break;
        case 100:
          this.progressColor = 'bg-[var(--btn-success)]';
          break;
        default:
          this.progressColor = '';
      }

      return nb;
    },
  },
  methods: {
    validatePasswordFinal() {
      let reg = new RegExp(
        this.rule.start +
          this.rule.majuscule +
          this.rule.minuscule +
          this.rule.chiffre +
          this.rule.special +
          this.rule.nbCharacter
      );
      let test = reg.test(this.password);
      if (test && this.password === this.passwordConfirm) {
        this.btnSubmit = false;
        this.classPasswordConfirm = 'is-valid';
        this.classPassword = 'is-valid';
      } else {
        this.btnSubmit = true;
      }

      if (this.password !== this.passwordConfirm) {
        this.classPasswordConfirm = 'is-invalid';
      }

      if (!test) {
        this.classPassword = 'is-invalid';
      } else {
        this.classPassword = 'is-valid';
      }
    },

    checkPassword() {
      this.checkNbCharacter();
      this.checkMajuscule();
      this.checkMinuscule();
      this.checkChiffre();
      this.checkSpecial();

      this.validatePasswordFinal();
    },

    /**
     * Vérifie le nombre de caractères du mot de passe
     */
    checkNbCharacter() {
      let reg = new RegExp(this.rule.start + this.rule.nbCharacter + this.rule.end);
      let test = reg.test(this.password);
      this.updateRender(test, this.nbCharacter);
    },

    /**
     * Vérifie la présence d'au moins 1 majuscule
     */
    checkMajuscule() {
      let reg = new RegExp(this.rule.majuscule);
      let test = reg.test(this.password);
      this.updateRender(test, this.majuscule);
    },

    /**
     * Vérifie la présence d'au moins 1 minuscule
     */
    checkMinuscule() {
      let reg = new RegExp(this.rule.minuscule);
      let test = reg.test(this.password);
      this.updateRender(test, this.minuscule);
    },

    /**
     * Vérifie la présence d'au moins 1 chiffre
     */
    checkChiffre() {
      let reg = new RegExp(this.rule.chiffre);
      let test = reg.test(this.password);
      this.updateRender(test, this.chiffre);
    },

    /**
     * Vérifie la présence d'un caractère spécial
     */
    checkSpecial() {
      let reg = new RegExp(this.rule.special);
      let test = reg.test(this.password);
      this.updateRender(test, this.special);
    },

    /**
     * Met à jour l'affichage
     * @param test
     * @param rule
     */
    updateRender(test, rule) {
      if (test) {
        rule.progress = 20;
        rule.class = 'text-[var(--input-valid)]';
        rule.icon = 'M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
      } else {
        rule.class = 'text-[var(--input-invalid)]';
        rule.icon = 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        rule.progress = 0;
      }
    },

    resetAll() {
      this.password = '';
      this.passwordConfirm = '';
      this.classPasswordConfirm = '';
      this.classPassword = '';
      this.btnSubmit = true;
      this.resetRender(this.nbCharacter);
      this.resetRender(this.majuscule);
      this.resetRender(this.minuscule);
      this.resetRender(this.chiffre);
      this.resetRender(this.special);
    },

    resetRender(rule) {
      rule.progress = 0;
      rule.class = 'text-[var(--input-invalid)]';
      rule.icon = 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
    },

    /**
     * Sauvegarde le nouveau mot de passe
     */
    savePassword() {
      this.loading = true;
      axios
        .post(this.url_change_password, {
          data: this.password,
        })
        .then((response) => {
          this.msgUpdatePassword = response.data.msg;
          this.redirect = response.data.redirect;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.resetAll();
          setTimeout(() => {
            this.msgUpdatePassword = '';
            if (this.redirect !== false) {
              window.location = this.redirect;
            }
          }, 4000);
        });
    },
  },
};
</script>

<template>
  <div>
    <div
      v-if="loading"
      class="bg-white dark:bg-slate-800 rounded-lg shadow-md border border-gray-200 dark:border-slate-700 p-8 w-full max-w-md"
    >
      <!-- Logo Skeleton -->
      <div class="text-center mb-8">
        <div class="h-9 w-40 bg-gray-200 dark:bg-slate-700 rounded mx-auto mb-2 animate-pulse"></div>
        <div class="h-5 w-56 bg-gray-200 dark:bg-slate-700 rounded mx-auto animate-pulse"></div>
      </div>

      <!-- Form Skeleton -->
      <div class="space-y-6">
        <!-- Email Field -->
        <div>
          <div class="h-5 w-32 bg-gray-200 dark:bg-slate-700 rounded mb-2 animate-pulse"></div>
          <div class="h-11 w-full bg-gray-200 dark:bg-slate-700 rounded-lg animate-pulse"></div>
        </div>

        <!-- Password Field -->
        <div>
          <div class="h-5 w-28 bg-gray-200 dark:bg-slate-700 rounded mb-2 animate-pulse"></div>
          <div class="h-11 w-full bg-gray-200 dark:bg-slate-700 rounded-lg animate-pulse"></div>
        </div>

        <!-- Remember & Forgot -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gray-200 dark:bg-slate-700 rounded animate-pulse"></div>
            <div class="h-4 w-32 bg-gray-200 dark:bg-slate-700 rounded animate-pulse"></div>
          </div>
          <div class="h-4 w-36 bg-gray-200 dark:bg-slate-700 rounded animate-pulse"></div>
        </div>

        <!-- Submit Button -->
        <div class="h-11 w-full bg-gray-200 dark:bg-slate-700 rounded-lg animate-pulse"></div>
      </div>
    </div>
    <div v-else :class="this.fullScreen ? 'flex gap-10' : ''">
      <div :class="this.fullScreen ? 'w-2/4' : ''">
        <div v-if="this.msgUpdatePassword !== ''" class="alert alert-primary-light">
          <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          <div class="alert-content">
            <div class="alert-message">{{ this.msgUpdatePassword }}</div>
          </div>
        </div>

        <div>
          <label for="input-password-1" class="lock mb-2 text-sm font-medium text-gray-900 dark:text-white">{{
            this.translate.password
          }}</label>
          <input
            type="password"
            class="form-input no-control"
            :class="this.classPassword"
            id="input-password-1"
            v-model="password"
            @keyup="this.checkPassword"
          />
        </div>
        <div>
          <label for="input-password-2" class="form-label">{{ this.translate.password_2 }}</label>
          <input
            type="password"
            class="form-input no-control"
            :class="this.classPasswordConfirm"
            id="input-password-2"
            v-model="passwordConfirm"
            @keyup="this.validatePasswordFinal"
          />
          <div v-if="this.classPasswordConfirm === 'is-invalid'" class="text-[var(--text-secondary)] text-sm mt-1">
            {{ this.translate.error_password_2 }}
          </div>
        </div>

        <button
          class="btn btn-secondary btn-md mt-4"
          :class="this.fullScreen ? 'float-end' : 'w-full'"
          :disabled="btnSubmit"
          @click="savePassword"
        >
          <svg class="icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-width="2"
              d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"
            ></path>
          </svg>
          {{ this.translate.btn_submit }}
        </button>
      </div>

      <div
        class="mt-4 max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 text-sm"
        :class="this.fullScreen ? 'ms-auto' : ''"
      >
        <p class="text-[var(--text-secondary)]">{{ this.translate.force }}</p>
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-2 mb-2">
          <div
            class="h-2.5 rounded-full"
            :class="this.progressColor"
            :style="'width: ' + this.progress + '%; transition: width 0.6s ease-in-out;'"
          ></div>
        </div>
        <div :class="this.nbCharacter.class">
          <svg
            class="float-left me-1 w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="this.nbCharacter.icon"
            />
          </svg>

          {{ this.translate.force_nb_character }}
        </div>
        <div :class="this.majuscule.class">
          <svg
            class="float-left me-1 w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="this.majuscule.icon"
            />
          </svg>
          {{ this.translate.force_majuscule }}
        </div>
        <div :class="this.minuscule.class">
          <svg
            class="float-left me-1 w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="this.minuscule.icon"
            />
          </svg>
          {{ this.translate.force_minuscule }}
        </div>
        <div :class="this.chiffre.class">
          <svg
            class="float-left me-1 w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="this.chiffre.icon"
            />
          </svg>
          {{ this.translate.force_chiffre }}
        </div>
        <div :class="this.special.class">
          <svg
            class="float-left me-1 w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="this.chiffre.icon"
            />
          </svg>
          {{ this.translate.force_character_spe }} - #?!@$%^&*-
        </div>
      </div>
    </div>
  </div>
</template>

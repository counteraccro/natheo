<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Changement du mot de passe pour un compte user
 */

import { defineComponent, type PropType } from 'vue';
import axios, { type AxiosError } from 'axios';
import type {
  ChangePasswordResponse,
  ChangePasswordTranslate,
  PasswordRuleState,
  PasswordRules,
} from '@/ts/User/ChangePassword.type';

const ICON_INVALID = 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
const ICON_VALID = 'M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';

/**
 * État initial (invalide) d'une règle de mot de passe
 */
function initRuleState(): PasswordRuleState {
  return { class: 'text-[var(--input-invalid)]', icon: ICON_INVALID, progress: 0 };
}

export default defineComponent({
  name: 'ChangePassword',
  props: {
    url_change_password: {
      type: String,
      required: true,
    },
    translate: {
      type: Object as PropType<ChangePasswordTranslate>,
      required: true,
    },
    fullScreen: {
      default: false,
      type: Boolean,
    },
    needCurrentPassword: {
      default: false,
      type: Boolean,
    },
  },
  data() {
    return {
      currentPassword: '' as string,
      password: '' as string,
      classPassword: '' as string,
      passwordConfirm: '' as string,
      classPasswordConfirm: '' as string,
      redirect: false as string | false,
      loading: false as boolean,
      btnSubmit: true as boolean,
      msgUpdatePassword: '' as string,
      nbCharacter: initRuleState(),
      majuscule: initRuleState(),
      minuscule: initRuleState(),
      chiffre: initRuleState(),
      special: initRuleState(),
      rule: {
        start: '^',
        end: '$',
        nbCharacter: '.{8,}',
        majuscule: '(?=.*[A-Z])',
        minuscule: '(?=.*?[a-z])',
        chiffre: '(?=.*?[0-9])',
        special: '(?=.*?[#?!@$%^&*-])',
      } as PasswordRules,
    };
  },
  computed: {
    progress(): number {
      return (
        this.nbCharacter.progress +
        this.majuscule.progress +
        this.minuscule.progress +
        this.chiffre.progress +
        this.special.progress
      );
    },

    progressColor(): string {
      switch (this.progress) {
        case 20:
        case 40:
          return 'bg-[var(--btn-danger)]';
        case 60:
        case 80:
          return 'bg-[var(--btn-warning)]';
        case 100:
          return 'bg-[var(--btn-success)]';
        default:
          return '';
      }
    },
  },
  methods: {
    validatePasswordFinal(): void {
      const reg = new RegExp(
        this.rule.start +
          this.rule.majuscule +
          this.rule.minuscule +
          this.rule.chiffre +
          this.rule.special +
          this.rule.nbCharacter
      );
      const test = reg.test(this.password);
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

    checkPassword(): void {
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
    checkNbCharacter(): void {
      const reg = new RegExp(this.rule.start + this.rule.nbCharacter + this.rule.end);
      const test = reg.test(this.password);
      this.updateRender(test, this.nbCharacter);
    },

    /**
     * Vérifie la présence d'au moins 1 majuscule
     */
    checkMajuscule(): void {
      const reg = new RegExp(this.rule.majuscule);
      const test = reg.test(this.password);
      this.updateRender(test, this.majuscule);
    },

    /**
     * Vérifie la présence d'au moins 1 minuscule
     */
    checkMinuscule(): void {
      const reg = new RegExp(this.rule.minuscule);
      const test = reg.test(this.password);
      this.updateRender(test, this.minuscule);
    },

    /**
     * Vérifie la présence d'au moins 1 chiffre
     */
    checkChiffre(): void {
      const reg = new RegExp(this.rule.chiffre);
      const test = reg.test(this.password);
      this.updateRender(test, this.chiffre);
    },

    /**
     * Vérifie la présence d'un caractère spécial
     */
    checkSpecial(): void {
      const reg = new RegExp(this.rule.special);
      const test = reg.test(this.password);
      this.updateRender(test, this.special);
    },

    /**
     * Met à jour l'affichage
     * @param test
     * @param rule
     */
    updateRender(test: boolean, rule: PasswordRuleState): void {
      if (test) {
        rule.progress = 20;
        rule.class = 'text-[var(--input-valid)]';
        rule.icon = ICON_VALID;
      } else {
        this.resetRender(rule);
      }
    },

    resetAll(): void {
      this.currentPassword = '';
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

    resetRender(rule: PasswordRuleState): void {
      Object.assign(rule, initRuleState());
    },

    /**
     * Sauvegarde le nouveau mot de passe
     */
    savePassword(): void {
      this.loading = true;
      axios
        .post<ChangePasswordResponse>(this.url_change_password, {
          data: this.password,
          current: this.currentPassword,
        })
        .then((response) => {
          this.msgUpdatePassword = response.data.msg;
          this.redirect = response.data.redirect;
        })
        .catch((error: AxiosError<ChangePasswordResponse>) => {
          this.msgUpdatePassword = error.response?.data?.msg ?? '';
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.resetAll();
          setTimeout(() => {
            this.msgUpdatePassword = '';
            if (this.redirect !== false) {
              window.location.href = this.redirect;
            }
          }, 4000);
        });
    },
  },
});
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
    <div v-else :class="fullScreen ? 'flex gap-10' : ''">
      <div :class="fullScreen ? 'w-2/4' : ''">
        <div v-if="msgUpdatePassword !== ''" class="alert alert-primary-light">
          <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          <div class="alert-content">
            <div class="alert-message">{{ msgUpdatePassword }}</div>
          </div>
        </div>

        <div v-if="needCurrentPassword">
          <label for="input-password-current" class="form-label">{{ translate.password_current }}</label>
          <input
            type="password"
            class="form-input no-control"
            id="input-password-current"
            autocomplete="current-password"
            v-model="currentPassword"
          />
        </div>
        <div>
          <label for="input-password-1" class="lock mb-2 text-sm font-medium text-gray-900 dark:text-white">{{
            translate.password
          }}</label>
          <input
            type="password"
            class="form-input no-control"
            :class="classPassword"
            id="input-password-1"
            v-model="password"
            @keyup="checkPassword"
          />
        </div>
        <div>
          <label for="input-password-2" class="form-label">{{ translate.password_2 }}</label>
          <input
            type="password"
            class="form-input no-control"
            :class="classPasswordConfirm"
            id="input-password-2"
            v-model="passwordConfirm"
            @keyup="validatePasswordFinal"
          />
          <div v-if="classPasswordConfirm === 'is-invalid'" class="text-[var(--text-secondary)] text-sm mt-1">
            {{ translate.error_password_2 }}
          </div>
        </div>

        <button
          class="btn btn-secondary btn-md mt-4"
          :class="fullScreen ? 'float-end' : 'w-full'"
          :disabled="btnSubmit || (needCurrentPassword && currentPassword === '')"
          @click="savePassword"
        >
          <svg class="icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-width="2"
              d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"
            ></path>
          </svg>
          {{ translate.btn_submit }}
        </button>
      </div>

      <div
        class="mt-4 max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 text-sm"
        :class="fullScreen ? 'ms-auto' : ''"
      >
        <p class="text-[var(--text-secondary)]">{{ translate.force }}</p>
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-2 mb-2">
          <div
            class="h-2.5 rounded-full"
            :class="progressColor"
            :style="'width: ' + progress + '%; transition: width 0.6s ease-in-out;'"
          ></div>
        </div>
        <div :class="nbCharacter.class">
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
              :d="nbCharacter.icon"
            />
          </svg>

          {{ translate.force_nb_character }}
        </div>
        <div :class="majuscule.class">
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
              :d="majuscule.icon"
            />
          </svg>
          {{ translate.force_majuscule }}
        </div>
        <div :class="minuscule.class">
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
              :d="minuscule.icon"
            />
          </svg>
          {{ translate.force_minuscule }}
        </div>
        <div :class="chiffre.class">
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
              :d="chiffre.icon"
            />
          </svg>
          {{ translate.force_chiffre }}
        </div>
        <div :class="special.class">
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
              :d="special.icon"
            />
          </svg>
          {{ translate.force_character_spe }} - #?!@$%^&*-
        </div>
      </div>
    </div>
  </div>
</template>

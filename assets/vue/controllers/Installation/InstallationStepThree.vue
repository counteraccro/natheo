<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Installation CMS - Etape 3 compte fondateur & finalisation
 */
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type {
  InstallationStepThreeUrls,
  InstallationStepThreeTranslate,
  InstallationStepThreeLocales,
  InstallationStepThreeDatas,
  UserPayload,
  CreateUserResponse,
  ApiActionResponse,
  ValidableField,
  FieldValidation,
  FinishStepKey,
  FinishStepStatus,
} from '@/ts/Installation/InstallationStepThree.type';
import axios, { AxiosError } from 'axios';
import SkeletonInstallationStepThree from '@/vue/Components/Skeleton/Installation/InstallationStepThree.vue';

export default defineComponent({
  name: 'InstallationStepThree',
  components: { SkeletonInstallationStepThree },
  props: {
    urls: {
      type: Object as PropType<InstallationStepThreeUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<InstallationStepThreeTranslate>,
      required: true,
    },
    locales: {
      type: Object as PropType<InstallationStepThreeLocales>,
      required: true,
    },
    datas: {
      type: Object as PropType<InstallationStepThreeDatas>,
      required: true,
    },
  },
  data() {
    return {
      initialLoading: true as boolean,
      userCreating: false as boolean,
      userSuccess: false as boolean,
      user: {
        login: '',
        email: '',
        password: '',
      } as UserPayload,
      valid: {
        login: null,
        email: null,
        password: null,
      } as FieldValidation,
      passwordStrength: null as 'weak' | 'normal' | 'strong' | null,
      showPassword: false as boolean,
      finishing: false as boolean,
      finished: false as boolean,
      finishStep: {
        config: { success: false, message: '' },
        installData: { success: false, message: '' },
        cache: { success: false, message: '' },
      } as FinishStepStatus,
    };
  },
  computed: {
    isUserFormValid(): boolean {
      return this.valid.login === true && this.valid.email === true && this.valid.password === true;
    },

    hasFinishError(): boolean {
      return Object.values(this.finishStep).some((step) => step.message !== '' && !step.success);
    },

    userHeaderAccentColor(): string {
      return this.userSuccess ? 'var(--btn-success)' : 'var(--primary)';
    },

    finishHeaderAccentColor(): string {
      if (this.finished) {
        return 'var(--btn-success)';
      }
      if (this.hasFinishError) {
        return 'var(--btn-danger)';
      }
      return 'var(--primary)';
    },
  },
  mounted() {
    this.initialLoading = false;
  },
  methods: {
    sanitizeLogin(event: Event): void {
      const target = event.target as HTMLInputElement;
      this.user.login = target.value.replace(/[^\w\s]/gi, '');
    },

    validateLogin(): void {
      this.valid.login = this.user.login.trim() !== '';
    },

    validateEmail(): void {
      this.valid.email = /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(this.user.email);
    },

    /**
     * Un mot de passe n'est considéré valide que s'il est "fort"
     * (identique à la règle historique de l'installateur).
     */
    validatePassword(): void {
      const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])(?=.{8,})/;
      const mediumRegex = /^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*\d))|((?=.*[A-Z])(?=.*\d)))(?=.{6,})/;

      if (strongRegex.test(this.user.password)) {
        this.passwordStrength = 'strong';
        this.valid.password = true;
      } else if (mediumRegex.test(this.user.password)) {
        this.passwordStrength = 'normal';
        this.valid.password = false;
      } else {
        this.passwordStrength = this.user.password === '' ? null : 'weak';
        this.valid.password = false;
      }
    },

    fieldBorderColor(field: ValidableField): string {
      if (this.valid[field] === null) {
        return 'var(--border-color)';
      }
      return this.valid[field] ? 'var(--btn-success)' : 'var(--btn-danger)';
    },

    /**
     * Crée le compte fondateur.
     */
    createUser(): void {
      this.userCreating = true;

      axios
        .post<CreateUserResponse>(this.urls.create_user, { user: this.user })
        .then((response) => {
          this.userSuccess = response.data.success;
        })
        .catch((error: AxiosError) => {
          console.error(error);
        })
        .finally(() => {
          this.userCreating = false;
        });
    },

    /**
     * Exécute une étape de finalisation (requête GET sans body) et stocke son résultat.
     */
    runFinishStep(key: FinishStepKey, url: string, successMessage: string, koMessage: string): Promise<void> {
      return axios
        .get<ApiActionResponse>(url)
        .then((response) => {
          this.finishStep[key] = {
            success: response.data.success,
            message: response.data.success ? successMessage : (response.data.error ?? koMessage),
          };
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.finishStep[key] = { success: false, message: error.message };
        });
    },

    /**
     * Enchaîne : passage en mode dev pour les fixtures, chargement des données,
     * nettoyage du cache, puis redirection vers l'authentification.
     */
    async finishInstallation(): Promise<void> {
      this.finishing = true;

      await this.runFinishStep(
        'config',
        this.urls.change_env,
        this.translate.finish_installation_loading_msg_config_success,
        this.translate.finish_installation_loading_msg_config_ko
      );
      if (!this.finishStep.config.success) {
        this.finishing = false;
        return;
      }

      await this.runFinishStep(
        'installData',
        this.urls.load_fixtures,
        this.translate.finish_installation_loading_msg_installation_data_success,
        this.translate.finish_installation_loading_msg_installation_data_ko
      );
      if (!this.finishStep.installData.success) {
        this.finishing = false;
        return;
      }

      await this.runFinishStep(
        'cache',
        this.urls.clear_cache,
        this.translate.finish_installation_loading_msg_cache_success,
        this.translate.finish_installation_loading_msg_cache_ko
      );
      if (!this.finishStep.cache.success) {
        this.finishing = false;
        return;
      }

      this.finished = true;
      this.finishing = false;

      setTimeout(() => {
        window.location.href = this.urls.step_4;
      }, 2500);
    },
  },
});
</script>

<template>
  <skeleton-installation-step-three v-if="initialLoading" />

  <div v-else>
    <h2 class="text-2xl font-bold mb-2">{{ translate.title_h1 }}</h2>
    <p class="text-sm mb-6" style="color: var(--text-secondary)">{{ translate.fondateur_description }}</p>

    <div
      v-if="datas.debug_mode"
      class="mb-6 px-4 py-3 rounded-lg text-sm"
      style="
        background-color: var(--alert-danger-bg);
        border: 1px solid var(--alert-danger-border);
        color: var(--alert-danger-text);
      "
    >
      <p class="font-semibold flex items-center gap-2 mb-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
          />
        </svg>
        {{ translate.debug_titre }}
      </p>
      <p class="mb-1">{{ translate.debug_texte_1 }}</p>
      <p class="mb-2">{{ translate.debug_texte_2 }}</p>
      <p class="mb-1">{{ translate.debug_texte_3 }}</p>
      <ul class="list-disc list-inside space-y-1">
        <li v-html="translate.debug_texte_4"></li>
        <li v-html="translate.debug_texte_5"></li>
        <li v-html="translate.debug_texte_6"></li>
      </ul>
    </div>

    <!-- Card : compte fondateur -->
    <div class="card rounded-xl overflow-hidden mb-6" :style="{ borderColor: userHeaderAccentColor }">
      <div
        class="card-header flex items-center justify-between gap-2 transition-colors"
        :style="{ backgroundColor: userHeaderAccentColor }"
      >
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" style="color: #ffffff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M18 9a3 3 0 11-6 0 3 3 0 016 0zM3 20a6 6 0 0112 0M19 8v4m2-2h-4"
            />
          </svg>
          <span class="text-sm font-semibold" style="color: #ffffff">{{ translate.fondateur_titre_card }}</span>
        </div>
        <svg
          v-if="userSuccess"
          class="w-4 h-4"
          style="color: #ffffff"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <div class="p-5 sm:p-6">
        <div class="grid grid-cols-1 gap-5 mb-5">
          <div class="form-group">
            <label class="form-label" for="user-email">{{ translate.fondateur_email_label }}</label>
            <input
              id="user-email"
              type="email"
              class="form-input form-input-sm"
              :style="{ borderColor: fieldBorderColor('email') }"
              v-model="user.email"
              :placeholder="translate.fondateur_email_placeholder"
              :disabled="userSuccess"
              @change="validateEmail()"
            />
            <span v-if="valid.email === false" class="form-text text-error"
              >✗ {{ translate.fondateur_email_error }}</span
            >
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div class="form-group">
            <label class="form-label" for="user-login">{{ translate.fondateur_login_label }}</label>
            <input
              id="user-login"
              type="text"
              class="form-input form-input-sm"
              :style="{ borderColor: fieldBorderColor('login') }"
              v-model="user.login"
              :placeholder="translate.fondateur_login_placeholder"
              :disabled="userSuccess"
              @keyup="sanitizeLogin"
              @change="validateLogin()"
            />
            <span v-if="valid.login === false" class="form-text text-error"
              >✗ {{ translate.fondateur_login_error }}</span
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="user-password">{{ translate.fondateur_password_label }}</label>
            <div class="relative">
              <input
                id="user-password"
                :type="showPassword ? 'text' : 'password'"
                maxlength="20"
                class="form-input form-input-sm w-full pr-10"
                :style="{ borderColor: fieldBorderColor('password') }"
                v-model="user.password"
                :disabled="userSuccess"
                @keyup="validatePassword()"
              />
              <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2"
                style="color: var(--text-light)"
                @click="showPassword = !showPassword"
              >
                <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.774 3.162 10.066 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
                  />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
              </button>
            </div>
            <span v-if="valid.password === false && passwordStrength !== null" class="form-text text-error"
              >✗ {{ translate.fondateur_password_error }}</span
            >
            <p class="text-xs mt-1.5" style="color: var(--text-light)">{{ translate.fondateur_password_help }}</p>
            <p
              v-if="passwordStrength === 'weak'"
              class="text-xs mt-1 flex items-center gap-1"
              style="color: var(--btn-danger)"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
              </svg>
              {{ translate.fondateur_password_weak }}
            </p>
            <p
              v-else-if="passwordStrength === 'normal'"
              class="text-xs mt-1 flex items-center gap-1"
              style="color: #f59e0b"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              {{ translate.fondateur_password_normal }}
            </p>
            <p
              v-else-if="passwordStrength === 'strong'"
              class="text-xs mt-1 flex items-center gap-1"
              style="color: var(--btn-success)"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              {{ translate.fondateur_password_strong }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="px-5 sm:px-6 py-4 flex items-center justify-end border-t"
        style="border-color: var(--border-color); background-color: var(--bg-hover)"
      >
        <button
          v-if="!userSuccess"
          type="button"
          class="btn btn-primary btn-sm"
          :disabled="!isUserFormValid || userCreating"
          @click="createUser()"
        >
          <svg v-if="userCreating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M18 9a3 3 0 11-6 0 3 3 0 016 0zM3 20a6 6 0 0112 0M19 8v4m2-2h-4"
            />
          </svg>
          <template v-if="userCreating">{{ translate.fondateur_loading_msg }}</template>
          <template v-else>{{ translate.fondateur_btn_create }}</template>
        </button>

        <span v-else class="flex items-center gap-1.5 text-sm font-medium" style="color: var(--btn-success)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          {{ translate.fondateur_success }}
        </span>
      </div>
    </div>

    <!-- Card : finalisation, visible une fois le compte créé -->
    <div v-if="userSuccess" class="card rounded-xl overflow-hidden">
      <div
        class="card-header flex items-center gap-2 transition-colors"
        :style="{ backgroundColor: finishHeaderAccentColor }"
      >
        <svg class="w-5 h-5" style="color: #ffffff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
        </svg>
        <span class="text-sm font-semibold" style="color: #ffffff">{{ translate.finish_installation_title }}</span>
      </div>

      <div class="p-5 sm:p-6">
        <p class="text-sm mb-1" style="color: var(--text-secondary)">{{ translate.finish_installation_text_1 }}</p>
        <p class="text-sm" style="color: var(--text-secondary)">{{ translate.finish_installation_text_2 }}</p>

        <div v-if="finishing || finished" class="mt-6 flex flex-col gap-2">
          <div v-for="key in ['config', 'installData', 'cache']" :key="key">
            <div
              v-if="finishStep[key].success"
              class="flex items-center gap-1.5 text-sm font-medium"
              style="color: var(--btn-success)"
            >
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ finishStep[key].message }}</span>
            </div>
            <div
              v-else-if="finishStep[key].message"
              class="px-4 py-3 rounded-lg flex items-start gap-2 text-sm"
              style="
                background-color: var(--alert-danger-bg);
                border: 1px solid var(--alert-danger-border);
                color: var(--alert-danger-text);
              "
            >
              <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>{{ finishStep[key].message }}</span>
            </div>
          </div>

          <div v-if="finished" class="flex items-center gap-1.5 text-sm font-medium" style="color: var(--btn-success)">
            <svg class="w-4 h-4 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>{{ translate.finish_installation_loading_msg_success }}</span>
          </div>
        </div>
      </div>

      <div
        v-if="!finished"
        class="px-5 sm:px-6 py-4 flex items-center justify-end border-t"
        style="border-color: var(--border-color); background-color: var(--bg-hover)"
      >
        <button type="button" class="btn btn-primary btn-sm" :disabled="finishing" @click="finishInstallation()">
          <svg v-if="finishing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5"
            />
          </svg>
          {{ translate.finish_installation_btn }}
        </button>
      </div>
    </div>
  </div>
</template>

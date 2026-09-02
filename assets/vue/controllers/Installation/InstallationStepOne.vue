<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Installation CMS - Etape 1 check base de données
 */
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type {
  InstallationStepOneUrls,
  InstallationStepOneTranslate,
  InstallationStepOneLocales,
  InstallationStepOneDatas,
  BddConfig,
  DataBaseConfigErrors,
  ValidableField,
  UpdateEnvPayload,
  UpdateEnvResponse,
  CheckActionResult,
  CheckActionBddResponse,
} from '@/ts/Installation/InstallationStepOne.type';
import axios, { AxiosError } from 'axios';
import SkeletonInstallationStepOne from '@/vue/Components/Skeleton/Installation/InstallationStepOne.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import { Toasts } from '@/ts/Toast/Toast.type';

export default defineComponent({
  name: 'InstallationStepOne',
  components: { Toast, SkeletonInstallationStepOne },
  props: {
    urls: {
      type: Object as PropType<InstallationStepOneUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<InstallationStepOneTranslate>,
      required: true,
    },
    locales: {
      type: Object as PropType<InstallationStepOneLocales>,
      required: true,
    },
    datas: {
      type: Object as PropType<InstallationStepOneDatas>,
      required: true,
    },
  },
  data() {
    return {
      initialLoading: true as boolean,
      loading: false as boolean,
      checking: false as boolean,
      tested: false as boolean,
      check: { success: false, message: '' } as CheckActionResult,
      dataBaseConfig: {} as BddConfig,
      errors: {
        login: '',
        password: '',
        ip: '',
        port: '',
        charset: '',
        version: '',
      } as DataBaseConfigErrors,
      toasts: {
        success: {
          show: false,
          msg: '',
        },
        error: {
          show: false,
          msg: '',
        },
      } as Toasts,
    };
  },
  computed: {
    isValid(): boolean {
      return Object.values(this.errors).every((error) => error === '');
    },

    headerAccentColor(): string {
      if (!this.tested) {
        return 'var(--primary)';
      }
      return this.check.success ? 'var(--btn-success)' : 'var(--btn-danger)';
    },
  },
  mounted() {
    this.dataBaseConfig = this.datas.bdd_config;
    this.checkConnexion().finally(() => {
      this.initialLoading = false;
    });
  },
  methods: {
    /**
     * Teste la connexion à la bdd et stocke le résultat.
     */
    checkConnexion(): Promise<void> {
      this.loading = true;
      this.checking = true;

      return axios
        .post<CheckActionBddResponse>(this.urls.check_action_bdd, {
          config: this.dataBaseConfig,
          action: this.datas.option_check.connexion,
        })
        .then((response) => {
          this.check = response.data.connexion;
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.check = { success: false, message: error.message };
        })
        .finally(() => {
          this.tested = true;
          this.checking = false;
          this.loading = false;
        });
    },

    /**
     * Mise à jour du fichier .env
     * @param type
     */
    updateConfigBddEnv(type: string): void {
      this.loading = true;

      const payload: UpdateEnvPayload = {
        config_key: this.datas.config_key.database_url,
        config: this.dataBaseConfig,
        type,
      };

      axios
        .post<UpdateEnvResponse>(this.urls.update_env, payload)
        .then((response) => {
          if (response.data.success) {
            this.toasts.success.msg = this.translate.config_bdd_loading_msg_update_file;
            this.toasts.success.show = true;
            this.checkConnexion();
          }
        })
        .catch((error: AxiosError) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Vérification d'un champ
     * @param field
     */
    validateField(field: ValidableField): void {
      this.errors[field] = this.dataBaseConfig[field].trim() === '' ? this.translate.field_required : '';
    },

    /**
     *  Valide tous les champs d'un coup, réutilise validateField()
     */
    validate(): boolean {
      const fields: ValidableField[] = ['login', 'password', 'ip', 'port'];
      fields.forEach((field) => this.validateField(field));

      return this.isValid;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <skeleton-installation-step-one v-if="initialLoading" />

  <div v-else>
    <div class="card rounded-xl overflow-hidden mb-4">
      <div
        class="card-header flex items-center justify-between gap-2 transition-colors"
        :style="{ backgroundColor: headerAccentColor }"
      >
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" style="color: #ffffff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
              d="M4 7v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7M4 7c0 1.657 3.582 3 8 3s8-1.343 8-3M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3m0 5c0 1.657-3.582 3-8 3s-8-1.343-8-3"
            />
          </svg>
          <span class="text-sm font-semibold" style="color: #ffffff">{{ translate.config_bdd_title }}</span>
        </div>

        <svg
          v-if="check.success"
          class="w-4 h-4"
          style="color: #ffffff"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
        <svg
          v-else-if="tested"
          class="w-4 h-4"
          style="color: #ffffff"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </div>

      <div class="p-5 sm:p-6">
        <!-- Section : identifiants -->
        <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color: var(--text-light)">
          {{ translate.create_bdd_sub_title_identifiant }}
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6 pb-6 border-b" style="border-color: var(--border-color)">
          <div class="form-group">
            <label class="form-label" for="bdd-type">{{ translate.config_bdd_input_type_label }}</label>
            <input id="bdd-type" type="text" class="form-input form-input-sm" v-model="dataBaseConfig.type" disabled />
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-login">{{ translate.config_bdd_input_login_label }}</label>
            <input
              id="bdd-login"
              type="text"
              class="form-input form-input-sm"
              :class="errors.login ? 'is-invalid' : ''"
              @blur="validateField('login')"
              v-model="dataBaseConfig.login"
            />
            <span v-if="errors.login" class="form-text text-error">✗ {{ errors.login }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-password">{{ translate.config_bdd_input_password_label }}</label>
            <input
              id="bdd-password"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.password"
              :class="errors.password ? 'is-invalid' : ''"
              @blur="validateField('password')"
            />
            <span v-if="errors.password" class="form-text text-error">✗ {{ errors.password }}</span>
          </div>
        </div>

        <!-- Section : serveur -->
        <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color: var(--text-light)">
          {{ translate.create_bdd_sub_title_server }}
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6 pb-6 border-b" style="border-color: var(--border-color)">
          <div class="form-group">
            <label class="form-label" for="bdd-ip">{{ translate.config_bdd_input_ip_label }}</label>
            <input
              id="bdd-ip"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.ip"
              :class="errors.ip ? 'is-invalid' : ''"
              @blur="validateField('ip')"
            />
            <span v-if="errors.ip" class="form-text text-error">✗ {{ errors.ip }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-port">{{ translate.config_bdd_input_port_label }}</label>
            <input
              id="bdd-port"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.port"
              :class="errors.port ? 'is-invalid' : ''"
              @blur="validateField('port')"
            />
            <span v-if="errors.port" class="form-text text-error">✗ {{ errors.port }}</span>
          </div>
        </div>

        <!-- Section : options avancées -->
        <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color: var(--text-light)">
          {{ translate.create_bdd_sub_title_options }}
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div class="form-group">
            <label class="form-label" for="bdd-charset">{{ translate.create_bdd_input_charset_label }}</label>
            <input
              id="bdd-charset"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.charset"
              :class="errors.charset ? 'is-invalid' : ''"
              @blur="validateField('charset')"
            />
            <span v-if="errors.charset" class="form-text text-error">✗ {{ errors.charset }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-version">{{ translate.create_bdd_input_version_label }}</label>
            <input
              id="bdd-version"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.version"
              :class="errors.version ? 'is-invalid' : ''"
              @blur="validateField('version')"
            />
            <span v-if="errors.version" class="form-text text-error">✗ {{ errors.version }}</span>
          </div>
        </div>

        <!-- Résultat : ligne compacte verte si connexion OK, alerte complète en cas d'erreur -->
        <div v-if="tested" class="mt-6 flex flex-col gap-2">
          <div
            v-if="check.success"
            class="flex items-center gap-1.5 text-sm font-medium"
            style="color: var(--btn-success)"
          >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ translate.config_bdd_loading_msg_test_connexion_success }}</span>
          </div>

          <div
            v-else
            class="px-4 py-3 rounded-lg flex items-start gap-2 text-sm"
            style="
              background-color: var(--alert-danger-bg);
              border: 1px solid var(--alert-danger-border);
              color: var(--alert-danger-text);
            "
          >
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>{{ check.message }}</span>
          </div>
        </div>
      </div>

      <div
        class="px-5 sm:px-6 py-4 flex items-center justify-between border-t"
        style="border-color: var(--border-color); background-color: var(--bg-hover)"
      >
        <a :href="urls.step_0" class="btn btn-outline-dark btn-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4" />
          </svg>
          {{ translate.btn_return }}
        </a>

        <div class="flex gap-1.5">
          <button
            type="button"
            class="btn btn-primary btn-sm"
            :disabled="!isValid || loading"
            @click="updateConfigBddEnv(datas.option_check.database_exist)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"
              ></path>
            </svg>
            {{ translate.config_bdd_btn_save_config }}
          </button>

          <!-- Une fois la connexion validée, ce bouton devient le passage à l'étape suivante -->
          <a v-if="tested && check.success" :href="urls.step_2" class="btn btn-primary btn-sm">
            {{ translate.btn_step_two }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>

          <button
            v-else
            type="button"
            class="btn btn-primary btn-sm"
            :disabled="!isValid || loading"
            @click="checkConnexion()"
          >
            <svg v-if="checking" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
              ></path>
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              ></path>
            </svg>

            <template v-if="checking">{{ translate.config_bdd_btn_test_config_loading }}</template>
            <template v-else>{{ translate.config_bdd_btn_test_config }}</template>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :type="'success'" :show="toasts.success.show" @close-toast="closeToast('success')">
      <template #body>
        <div v-html="toasts.success.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.error.show" @close-toast="closeToast('error')">
      <template #body>
        <div v-html="toasts.error.msg"></div>
      </template>
    </toast>
  </div>
</template>

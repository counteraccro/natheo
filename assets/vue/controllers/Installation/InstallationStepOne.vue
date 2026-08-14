<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Installation CMS - Etape 1 check base de données
 */
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import {
  InstallationStepOneUrls,
  InstallationStepOneTranslate,
  InstallationStepOneLocales,
  InstallationStepOneDatas,
  CheckDatabaseResponse,
  BddConfig,
  DataBaseConfigErrors,
  ValidableField,
  UpdateEnvPayload,
  UpdateEnvResponse,
} from '@/ts/Installation/InstallationStepOne';
import axios, { AxiosError } from 'axios';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import AlertBase from '@/vue/Components/Alert/AlertBase.vue';
import SkeletonInstallationStepOne from '@/vue/Components/Skeleton/Installation/InstallationStepOne.vue';

export default defineComponent({
  name: 'InstallationStepOne',
  components: { SkeletonInstallationStepOne, AlertBase, AlertDanger },
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
      loading: false as boolean,
      isDataBaseConnected: null as boolean | null,
      dataBaseConfig: {} as BddConfig,
      errors: {
        login: '',
        password: '',
        ip: '',
        port: '',
        charset: '',
        version: '',
      } as DataBaseConfigErrors,
    };
  },
  mounted(): any {
    this.dataBaseConfig = this.datas.bdd_config;
    this.checkActionBdd(this.datas.option_check.connexion);
  },

  computed: {
    isValid(): boolean {
      return Object.values(this.errors).every((error) => error === '');
    },
  },

  methods: {
    /**
     * Test si la connexion est valide ou non
     */
    checkActionBdd(action: string): void {
      this.loading = true;
      axios
        .post<CheckDatabaseResponse>(this.urls.check_action_bdd, {
          config: this.dataBaseConfig,
          action,
        })
        .then((response) => {
          this.isDataBaseConnected = response.data.connexion;
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.isDataBaseConnected = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Mise à jour du fichier .env
     * @param type
     * @param onComplete
     */
    updateConfigBddEnv(type: string, onComplete: () => void): void {
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
            onComplete();
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
  },
});
</script>

<template>
  <skeleton-installation-step-one v-if="loading" />

  <div v-else>
    <div class="card mb-4">
      <div class="card-header" style="background-color: var(--primary)">
        <div class="card-title" style="color: #ffffff">
          <svg class="card-icon" style="color: #ffffff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
              d="M4 7v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7M4 7c0 1.657 3.582 3 8 3s8-1.343 8-3M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3m0 5c0 1.657-3.582 3-8 3s-8-1.343-8-3"
            />
          </svg>
          {{ translate.config_bdd_title }}
        </div>
      </div>
      <div class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-2">
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div class="form-group">
            <label class="form-label" for="bdd-type">{{ translate.config_bdd_input_ip_label }}</label>
            <input
              id="bdd-type"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.ip"
              :class="errors.ip ? 'is-invalid' : ''"
              @blur="validateField('ip')"
            />
            <span v-if="errors.ip" class="form-text text-error">✗ {{ errors.ip }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-type">{{ translate.config_bdd_input_port_label }}</label>
            <input
              id="bdd-type"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.port"
              :class="errors.port ? 'is-invalid' : ''"
              @blur="validateField('port')"
            />
            <span v-if="errors.port" class="form-text text-error">✗ {{ errors.port }}</span>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div class="form-group">
            <label class="form-label" for="bdd-type">{{ translate.create_bdd_input_charset_label }}</label>
            <input
              id="bdd-type"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.charset"
              :class="errors.charset ? 'is-invalid' : ''"
              @blur="validateField('charset')"
            />
            <span v-if="errors.charset" class="form-text text-error">✗ {{ errors.charset }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-type">{{ translate.create_bdd_input_version_label }}</label>
            <input
              id="bdd-type"
              type="text"
              class="form-input form-input-sm"
              v-model="dataBaseConfig.version"
              :class="errors.version ? 'is-invalid' : ''"
              @blur="validateField('version')"
            />
            <span v-if="errors.version" class="form-text text-error">✗ {{ errors.version }}</span>
          </div>
        </div>

        <alert-base type="alert-danger-light" text="toto" />
      </div>
      <div
        class="px-5 sm:px-6 py-4 flex items-center justify-between border-t"
        style="border-color: var(--border-color); background-color: var(--bg-hover)"
      >
        <a :href="urls.step_0" class="btn btn-outline-dark btn-sm">
          <svg
            class="w-4 h-4"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 12h14M5 12l4-4m-4 4 4 4"
            />
          </svg>
          {{ translate.btn_return }}
        </a>

        <div class="flex gap-1.5">
          <button
            type="button"
            class="btn btn-primary btn-sm"
            :disabled="!isValid"
            @click="checkActionBdd(datas.option_check.connexion)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            {{ translate.config_bdd_btn_test_config }}
          </button>

          <button
            type="button"
            class="btn btn-primary btn-sm"
            :disabled="!isValid"
            @click="updateConfigBddEnv(datas.option_check.database_exist, () => {})"
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
        </div>
      </div>
    </div>
  </div>
</template>

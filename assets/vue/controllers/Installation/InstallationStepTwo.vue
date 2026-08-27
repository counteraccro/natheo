<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Installation CMS - Etape 2 création de la base de données
 */
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type {
  InstallationStepTwoUrls,
  InstallationStepTwoTranslate,
  InstallationStepTwoLocales,
  InstallationStepTwoDatas,
  ValidableField,
  DataBaseFieldErrors,
  CheckActionBddResponse,
  ApiActionResponse,
  CreateStepKey,
  CreateStepStatus,
  UpdateEnvPayload,
  UpdateEnvResponse,
} from '@/ts/Installation/InstallationStepTwo';
import axios, { AxiosError } from 'axios';
import InstallationStepTwoSkeleton from '@/vue/Components/Skeleton/Installation/InstallationStepTwo.vue';
import { BddConfig } from '@/ts/Installation/InstallationStepOne';

export default defineComponent({
  name: 'InstallationStepTwo',
  components: { InstallationStepTwoSkeleton },
  props: {
    urls: {
      type: Object as PropType<InstallationStepTwoUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<InstallationStepTwoTranslate>,
      required: true,
    },
    locales: {
      type: Object as PropType<InstallationStepTwoLocales>,
      required: true,
    },
    datas: {
      type: Object as PropType<InstallationStepTwoDatas>,
      required: true,
    },
  },
  data() {
    return {
      initialLoading: true as boolean,
      loading: false as boolean,
      running: false as boolean,
      finished: false as boolean,
      bddConfig: {} as BddConfig,
      errors: {
        bdd_name: '',
        version: '',
        charset: '',
      } as DataBaseFieldErrors,
      createStep: {
        updateEnv: { success: false, message: '' },
        updateSecret: { success: false, message: '' },
        createBdd: { success: false, message: '' },
        createTable: { success: false, message: '' },
      } as CreateStepStatus,
    };
  },
  computed: {
    isValid(): boolean {
      return Object.values(this.errors).every((error) => error === '');
    },

    hasError(): boolean {
      return Object.values(this.createStep).some((step) => step.message !== '' && !step.success);
    },

    headerAccentColor(): string {
      if (this.finished) {
        return 'var(--btn-success)';
      }
      if (this.hasError) {
        return 'var(--btn-danger)';
      }
      return 'var(--primary)';
    },
  },
  mounted() {
    this.bddConfig = this.datas.bdd_config;
    this.initialLoading = false;
  },
  methods: {
    /**
     * Vérifie un champ
     */
    validateField(field: ValidableField): void {
      this.errors[field] = this.bddConfig[field].trim() === '' ? this.translate.field_required : '';
    },

    /**
     * Valide tous les champs d'un coup, réutilise validateField()
     */
    validate(): boolean {
      const fields: ValidableField[] = ['bdd_name', 'version', 'charset'];
      fields.forEach((field) => this.validateField(field));

      return this.isValid;
    },

    /**
     * Exécute une étape (requête GET sans body) et stocke son résultat.
     */
    runGetStep(key: CreateStepKey, url: string, successMessage: string, koMessage: string): Promise<void> {
      return axios
        .get<ApiActionResponse>(url)
        .then((response) => {
          this.createStep[key] = {
            success: response.data.success,
            message: response.data.success ? successMessage : (response.data.error ?? koMessage),
          };
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.createStep[key] = { success: false, message: error.message };
        });
    },

    /**
     * Persiste la configuration (DATABASE_URL + NATHEO_SCHEMA) dans le .env.
     * Toujours appelée, que la base existe déjà ou non.
     */
    updateEnv(): Promise<void> {
      const payload: UpdateEnvPayload = {
        config_key: this.datas.config_key.database_url,
        config: this.bddConfig,
        type: this.datas.option_check.database_exist,
      };

      return axios
        .post<UpdateEnvResponse>(this.urls.update_env, payload)
        .then((response) => {
          this.createStep.updateEnv = {
            success: response.data.success,
            message: response.data.success
              ? this.translate.create_bdd_loading_msg_update_file
              : (response.data.error ?? this.translate.create_bdd_loading_msg_update_file),
          };
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.createStep.updateEnv = { success: false, message: error.message };
        });
    },

    /**
     * Persiste la config, régénère APP_SECRET, crée la base si elle n'existe pas
     * encore, puis crée les tables dans tous les cas.
     */
    async createDatabase(): Promise<void> {
      if (!this.validate()) {
        return;
      }

      this.loading = true;
      this.running = true;

      const alreadyExists = await axios
        .post<CheckActionBddResponse>(this.urls.check_action_bdd, {
          config: this.bddConfig,
          action: this.datas.option_check.database_exist,
        })
        .then((response) => response.data.database_exist.success)
        .catch((error: AxiosError) => {
          console.error(error);
          return false;
        });

      await this.updateEnv();
      if (!this.createStep.updateEnv.success) {
        this.stopRunning();
        return;
      }

      await this.runGetStep(
        'updateSecret',
        this.urls.update_app_secret,
        this.translate.create_bdd_loading_msg_update_secret_success,
        this.translate.create_bdd_loading_msg_update_secret_ko
      );
      if (!this.createStep.updateSecret.success) {
        this.stopRunning();
        return;
      }

      // La base n'est créée que si elle n'existe pas déjà ;
      // les tables, elles, sont toujours (re)créées.
      if (alreadyExists) {
        this.createStep.createBdd = {
          success: true,
          message: this.translate.create_bdd_msg_already_exist,
        };
      } else {
        await this.runGetStep(
          'createBdd',
          this.urls.create_bdd,
          this.translate.create_bdd_loading_msg_create_bdd_success,
          this.translate.create_bdd_loading_msg_create_bdd_ko
        );
        if (!this.createStep.createBdd.success) {
          this.stopRunning();
          return;
        }
      }

      await this.runGetStep(
        'createTable',
        this.urls.create_schema,
        this.translate.create_bdd_loading_msg_create_table_success,
        this.translate.create_bdd_loading_msg_create_table_ko
      );
      if (!this.createStep.createTable.success) {
        this.stopRunning();
        return;
      }

      this.finishSuccess();
    },

    /**
     * Arrête la progression suite à une erreur.
     */
    stopRunning(): void {
      this.running = false;
      this.loading = false;
    },

    /**
     * Toutes les étapes nécessaires ont réussi : affiche le message de succès
     * puis redirige vers l'étape suivante.
     */
    finishSuccess(): void {
      this.finished = true;
      this.running = false;
      this.loading = false;

      setTimeout(() => {
        window.location.href = this.urls.step_3;
      }, 1200);
    },
  },
});
</script>

<template>
  <installation-step-two-skeleton v-if="initialLoading" />

  <div v-else>
    <h2 class="text-2xl font-bold mb-2">{{ translate.create_bdd_h1 }}</h2>
    <p class="text-sm mb-6" style="color: var(--text-secondary)">
      {{ translate.create_bdd_description_1 }}<br />
      {{ translate.create_bdd_description_2 }}
    </p>

    <div
      class="mb-6 px-4 py-3 rounded-lg text-sm"
      style="
        background-color: var(--alert-info-bg);
        border: 1px solid var(--alert-info-border);
        color: var(--alert-info-text);
      "
    >
      <p class="font-semibold flex items-center gap-2 mb-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        {{ translate.create_bdd_alert_title }}
      </p>
      <p class="mb-2">{{ translate.create_bdd_alert_text_1 }}</p>
      <ul class="list-disc list-inside space-y-1 mb-2">
        <li>
          {{ translate.create_bdd_alert_text_2 }} : <strong>{{ datas.bdd_params.database_schema }}</strong>
        </li>
        <li>
          {{ translate.create_bdd_alert_text_3 }} :
          <strong
            v-html="
              datas.bdd_params.database_prefix === ''
                ? translate.create_bdd_alert_text_4
                : datas.bdd_params.database_prefix + '_'
            "
          ></strong>
        </li>
      </ul>
      <p v-html="translate.create_bdd_alert_text_5"></p>
    </div>

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
              d="M4 7v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7M4 7c0 1.657 3.582 3 8 3s8-1.343 8-3M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3m0 5c0 1.657-3.582 3-8 3s-8-1.343-8-3M12 11v6m-3-3h6"
            />
          </svg>
          <span class="text-sm font-semibold" style="color: #ffffff">{{ translate.create_bdd_title }}</span>
        </div>

        <svg
          v-if="finished"
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <div class="form-group">
            <label class="form-label" for="bdd-name">{{ translate.create_bdd_input_bdd_name_label }}</label>
            <input
              id="bdd-name"
              type="text"
              class="form-input form-input-sm"
              :class="errors.bdd_name ? 'is-invalid' : ''"
              v-model="bddConfig.bdd_name"
              :disabled="running || finished"
              @blur="validateField('bdd_name')"
            />
            <span v-if="errors.bdd_name" class="form-text text-error">✗ {{ errors.bdd_name }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-version">{{ translate.create_bdd_input_version_label }}</label>
            <input
              id="bdd-version"
              type="text"
              class="form-input form-input-sm"
              :class="errors.version ? 'is-invalid' : ''"
              v-model="bddConfig.version"
              :disabled="running || finished"
              @blur="validateField('version')"
            />
            <span v-if="errors.version" class="form-text text-error">✗ {{ errors.version }}</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="bdd-charset">{{ translate.create_bdd_input_charset_label }}</label>
            <input
              id="bdd-charset"
              type="text"
              class="form-input form-input-sm"
              :class="errors.charset ? 'is-invalid' : ''"
              v-model="bddConfig.charset"
              :disabled="running || finished"
              @blur="validateField('charset')"
            />
            <span v-if="errors.charset" class="form-text text-error">✗ {{ errors.charset }}</span>
          </div>
        </div>

        <!-- Progression de la création -->
        <div v-if="running || finished" class="mt-6 flex flex-col gap-2">
          <div v-for="key in ['updateEnv', 'updateSecret', 'createBdd', 'createTable']" :key="key">
            <div
              v-if="createStep[key].success"
              class="flex items-center gap-1.5 text-sm font-medium"
              style="color: var(--btn-success)"
            >
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ createStep[key].message }}</span>
            </div>
            <div
              v-else-if="createStep[key].message"
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
              <span>{{ createStep[key].message }}</span>
            </div>
          </div>

          <div v-if="finished" class="flex items-center gap-1.5 text-sm font-medium" style="color: var(--btn-success)">
            <svg class="w-4 h-4 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>{{ translate.create_bdd_loading_msg_success }}</span>
          </div>
        </div>
      </div>

      <div
        class="px-5 sm:px-6 py-4 flex items-center justify-between border-t"
        style="border-color: var(--border-color); background-color: var(--bg-hover)"
      >
        <a :href="urls.step_1" class="btn btn-outline-dark btn-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4" />
          </svg>
          {{ translate.btn_return }}
        </a>

        <button
          v-if="!finished"
          type="button"
          class="btn btn-primary btn-sm"
          :disabled="running"
          @click="createDatabase()"
        >
          <svg v-if="running" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ translate.create_bdd_btn_create }}
        </button>
      </div>
    </div>
  </div>
</template>

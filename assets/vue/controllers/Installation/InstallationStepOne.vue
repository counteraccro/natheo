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
  Check,
  CheckAction,
  CheckActionBddResponse,
} from '@/ts/Installation/InstallationStepOne';
import axios, { AxiosError } from 'axios';
import SkeletonInstallationStepOne from '@/vue/Components/Skeleton/Installation/InstallationStepOne.vue';

export default defineComponent({
  name: 'InstallationStepOne',
  components: { SkeletonInstallationStepOne },
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
      currentCheck: null as CheckAction | null,
      tested: {
        connexion: false,
        database_exist: false,
      } as Record<CheckAction, boolean>,
      check: {
        connexion: { success: false, message: '' },
        database_exist: { success: false, message: '' },
      } as Check,
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
  computed: {
    isValid(): boolean {
      return Object.values(this.errors).every((error) => error === '');
    },

    // Pilote la couleur du header de card : neutre tant que rien n'est testé,
    // vert si la connexion est OK, rouge si elle échoue.
    headerAccentColor(): string {
      if (!this.tested.connexion) {
        return 'var(--primary)';
      }
      return this.check.connexion.success ? 'var(--btn-success)' : 'var(--btn-danger)';
    },
  },
  mounted() {
    this.dataBaseConfig = this.datas.bdd_config;
    this.runChecks().finally(() => {
      this.initialLoading = false;
    });
  },
  methods: {
    /**
     * Exécute un seul check et stocke son résultat.
     * Ne gère pas `loading` elle-même : c'est l'orchestrateur (runChecks) qui le pilote,
     * pour éviter de dupliquer la logique de chargement à chaque étape.
     */
    checkAction(action: CheckAction): Promise<void> {
      return axios
        .post<CheckActionBddResponse>(this.urls.check_action_bdd, {
          config: this.dataBaseConfig,
          action,
        })
        .then((response) => {
          const result = response.data[action];
          if (result) {
            this.check[action] = result;
            this.tested[action] = result.success;
          }
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.check[action] = { success: false, message: error.message };
        })
        .finally(() => {});
    },

    /**
     * Enchaîne les tests : connexion, puis existence de la base si la connexion a réussi.
     * Le bouton "Tester la connexion" lit `currentCheck` pour afficher l'étape en cours.
     */
    async runChecks(): Promise<void> {
      this.loading = true;
      this.currentCheck = this.datas.option_check.connexion;
      await this.checkAction(this.datas.option_check.connexion);
      this.check.database_exist = { success: false, message: '' };

      if (this.check.connexion.success) {
        this.currentCheck = this.datas.option_check.database_exist;
        await this.checkAction(this.datas.option_check.database_exist);
      }

      this.currentCheck = null;
      this.loading = false;
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
          v-if="check.connexion.success"
          class="w-4 h-4"
          style="color: #ffffff"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
        <svg
          v-else-if="check.connexion.message"
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

        <!-- Résultats : ligne compacte verte si connexion OK, alerte complète uniquement en cas d'erreur -->
        <div class="mt-6 flex flex-col gap-2">
          <div
            v-if="check.connexion.success"
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
            <span>{{ check.connexion.message || translate.config_bdd_loading_msg_test_connexion_ko }}</span>
          </div>

          <div
            v-if="check.database_exist.success"
            class="flex items-center gap-1.5 text-sm font-medium"
            style="color: var(--btn-success)"
          >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ 'La base de données existe. - A traduire' }}</span>
          </div>

          <div
            v-else-if="check.database_exist.message !== ''"
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
            <span>{{ check.database_exist.message }}</span>
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
          <button type="button" class="btn btn-primary btn-sm" :disabled="!isValid || loading" @click="runChecks()">
            <svg v-if="currentCheck" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
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

            <template v-if="currentCheck === 'connexion'">{{
              translate.config_bdd_loading_msg_test_connexion
            }}</template>
            <template v-else-if="currentCheck === 'database_exist'">{{
              translate.config_bdd_loading_msg_check_database
            }}</template>
            <template v-else>{{ translate.config_bdd_btn_test_config }}</template>
          </button>

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
        </div>
      </div>
    </div>
  </div>
</template>

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
} from '@/ts/Installation/InstallationStepOne';
import axios, { AxiosError } from 'axios';

export default defineComponent({
  name: 'InstallationStepOne',
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
    };
  },
  mounted(): any {
    this.dataBaseConfig = this.datas.bdd_config;
    this.checkDataBaseConnexion();
  },
  methods: {
    /**
     * Test si la connexion est valide ou non
     */
    checkDataBaseConnexion(): void {
      this.loading = true;

      axios
        .get<CheckDatabaseResponse>(this.urls.check_database)
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
  },
});
</script>

<template>
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
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
        <div class="form-group">
          <label class="form-label" for="bdd-type">{{ translate.config_bdd_input_type_label }}</label>
          <input id="bdd-type" type="text" class="form-input" v-model="dataBaseConfig.type" disabled />
        </div>

        <div class="form-group">
          <label class="form-label" for="bdd-login">{{ translate.config_bdd_input_login_label }}</label>
          <input id="bdd-login" type="text" class="form-input" v-model="dataBaseConfig.login" />
        </div>

        <div class="form-group">
          <label class="form-label" for="bdd-password">{{ translate.config_bdd_input_password_label }}</label>
          <input id="bdd-password" type="text" class="form-input" v-model="dataBaseConfig.password" />
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="form-group">
          <label class="form-label" for="bdd-type">{{ translate.config_bdd_input_ip_label }}</label>
          <input id="bdd-type" type="text" class="form-input" v-model="dataBaseConfig.ip" />
        </div>

        <div class="form-group">
          <label class="form-label" for="bdd-type">{{ translate.config_bdd_input_port_label }}</label>
          <input id="bdd-type" type="text" class="form-input" v-model="dataBaseConfig.port" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";

export default {
  name: "Installation-step-one",
  components: {
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    datas: Object
  },
  data() {
    return {
      bddConfig: [],
      loading: false,
      testConnexion: {
        isConnected: null,
        loading: false,
        updateFile: 0,
        testConn: 0
      },
      createDatabase: {
        valideName: null,
        valideVersion: null,
        updateFile: null,
        updateSecret: null,
        createBdd: null,
        createTable: null,
        redirect : null,
        error : null,
        showBtn: true,
      },
    }
  },
  mounted() {
    this.bddConfig = this.datas.bdd_config;
    this.checkConnexion();

    if(this.bddConfig.version !== "") {
      this.createDatabase.valideVersion = true
    }
    if(this.bddConfig.bdd_name !== "") {
      this.createDatabase.valideName = true
    }
  },

  computed: {},

  methods: {

    /**
     * Mise à jour fichier env
     */
    updateConfigBddEnv(type) {

      this.testConnexion.loading = true;
      this.testConnexion.updateFile = 1;
      this.testConnexion.testConn = 1;
      this.testConnexion.isConnected = null;

      axios.post(this.urls.update_env, {
        'config_key': this.datas.config_key.database_url,
        'config': this.bddConfig,
        'type': type
      }).then((response) => {

        if(response.data.success) {
          this.testConnexion.updateFile = 2;
          this.checkConnexion();
        }
        else {
          this.testConnexion.updateFile = 3;
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {

      });
    },

    /**
     * Test la connexion à la bdd
     */
    checkConnexion() {
      this.testConnexion.testConn = 1;
      axios.get(this.urls.check_database).then((response) => {

        this.testConnexion.isConnected = response.data.connexion;
        if (this.testConnexion.isConnected) {
          this.testConnexion.testConn = 2;
        } else {
          this.testConnexion.testConn = 3;
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.testConnexion.loading = false;
      });
    },

    isConnectedCardBorder() {
      if (this.testConnexion.isConnected === null) {
        return "";
      }

      if (this.testConnexion.isConnected) {
        return "border-success";
      } else {
        return "border-danger";
      }
    },

    /**
     * Met à jour le fieldset en fonction de isConnected
     * @return {string}
     */
    isConnectedCardHeaderClass() {
      if (this.testConnexion.isConnected === null) {
        return "";
      }

      if (this.testConnexion.isConnected) {
        return "text-bg-success";
      } else {
        return "text-bg-danger";
      }

    },

    /**
     * Met à jour le legend en fonction de isConnected
     * @return {string}
     */
    isConnectedCardHeader() {
      if (this.testConnexion.isConnected === null) {
        return '<i class="bi bi-database-gear"></i> ' + this.translate.config_bdd_title;
      }

      if (this.testConnexion.isConnected) {
        return '<strong><i class="bi bi-database-check"></i> ' + this.translate.config_bdd_title + '</strong>';
      } else {
        return '<strong><i class="bi bi-database-x"></i> ' + this.translate.config_bdd_title + '</strong>';
      }
    },

    /**
     * Vérifie et valide le nom de la bdd
     */
    sanitizeBddName(event) {
      this.bddConfig.bdd_name = event.target.value.replace(/[^\w\s]/gi, '');
    },

    /**
     * Vérifie si le champ est vide ou non
     * @param event
     * @param type
     */
    checkValideField(event, type) {
      let value = event.target.value;
      this.createDatabase[type] = !(value === "" || value.length === 0);
    },

    /**
     * Renvoi une class en fonction du type
     * @param type
     * @return {string}
     */
    isValideInput(type) {

      if (this.createDatabase[type] === null) {
        return "";
      }

      if (!this.createDatabase[type]) {
        return 'is-invalid';
      } else {
        return 'is-valid';
      }
    },

    /**
     * Active ou désactive le bouton "créer la bdd"
     * @return {string}
     */
    canCreateBdd()
    {
      if(this.createDatabase.valideName && this.createDatabase.valideVersion)
      {
        return "";
      }
      return "disabled";
    },

    /**
     * Création de l'ensemble des données (table + schema)
     */
    createAllDataBdd()
    {
      this.createDatabase.updateFile = 1;
      this.createDatabase.createBdd = null;
      this.createDatabase.createTable = null;
      this.createDatabase.redirect = null;
      this.createDatabase.updateSecret = null;
      this.createDatabase.showBtn = false;

      axios.post(this.urls.update_env, {
        'config_key': this.datas.config_key.database_url,
        'config': this.bddConfig,
        'type': this.datas.option_connexion.create_database
      }).then((response) => {
        if(response.data.success) {
          this.createDatabase.updateFile = 2;
          this.updateSecret();
        }
        else {
          this.createDatabase.updateFile = 3;
          this.createDatabase.error = response.data.error;
          this.createDatabase.showBtn = true;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {

      });
    },

    /**
     * Met à jour APP_SECRET
     */
    updateSecret()
    {
      this.createDatabase.updateSecret = 1;
      axios.get(this.urls.update_app_secret, {}).then((response) => {
        if(response.data.success) {
          this.createDatabase.updateSecret = 2;
          this.createDatabaseCom();
        }
        else {
          this.createDatabase.updateSecret = 3;
          this.createDatabase.error = response.data.error;
          this.createDatabase.showBtn = true;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {

      });
    },

    /** Création de la base de donnée **/
    createDatabaseCom()
    {
      this.createDatabase.createBdd = 1;
      axios.get(this.urls.create_bdd, {}).then((response) => {
        if(response.data.success) {
          this.createDatabase.createBdd = 2;
          this.createSchema();
        }
        else {
          this.createDatabase.createBdd = 3;
          this.createDatabase.error = response.data.error;
          this.createDatabase.showBtn = true;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {

      });
    },

    /** Création de la base de donnée **/
    createSchema()
    {
      this.createDatabase.createTable = 1;
      axios.get(this.urls.create_schema, {}).then((response) => {
        if(response.data.success) {
          this.createDatabase.createTable = 2;
          this.createDatabase.redirect = true;
        }
        else {
          this.createDatabase.createTable = 3;
          this.createDatabase.error = response.data.error;
          this.createDatabase.showBtn = true;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        document.location.href= this.urls.step_2;
      });
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

    <h1 class="text-body-emphasis">{{ this.translate.title_thx_h1 }}</h1>
    <p>
      {{ this.translate.description_thx_1 }} <br/>
    </p>

    <h1 class="text-body-emphasis">{{ this.translate.title_h1 }}</h1>
    <p>
      {{ this.translate.description_1 }} <br/>
      {{ this.translate.description_2 }}
    </p>

    <div class="card" :class="this.isConnectedCardBorder()">
      <div class="card-header" :class="this.isConnectedCardHeaderClass()" v-html="this.isConnectedCardHeader()">
      </div>
      <div class="card-body">

        <div class="row">

          <div class="col">
            <div class="mb-3">
              <label for="bdd-config-type" class="form-label">{{ this.translate.config_bdd_input_type_label }}</label>
              <input type="text" class="form-control" id="bdd-config-type" v-model="this.bddConfig.type" disabled>
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label for="bdd-config-login" class="form-label">{{ this.translate.config_bdd_input_login_label }}</label>
              <input type="text" class="form-control" id="bdd-config-login" v-model="this.bddConfig.login">
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label for="bdd-config-password" class="form-label">{{ this.translate.config_bdd_input_password_label }}</label>
              <input type="text" class="form-control" id="bdd-config-password" v-model="this.bddConfig.password">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label for="bdd-config-password" class="form-label">{{ this.translate.config_bdd_input_ip_label }}</label>
              <input type="text" class="form-control" id="bdd-config-password" v-model="this.bddConfig.ip">
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label for="bdd-config-password" class="form-label">{{ this.translate.config_bdd_input_port_label }}</label>
              <input type="text" class="form-control" id="bdd-config-password" v-model="this.bddConfig.port">
            </div>
          </div>
          <div class="col">
          </div>
        </div>
      </div>

      <div class="card-footer text-body-secondary">

        <div class="row">

          <div class="col">
            <div v-if="this.testConnexion.testConn === 1">
              <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
              <i>&nbsp;{{ this.translate.config_bdd_loading_msg_test_connexion }}</i>
            </div>
            <div v-else-if="this.testConnexion.testConn === 2">
              <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.config_bdd_loading_msg_test_connexion_success }}</span>
            </div>
            <div v-else-if="this.testConnexion.testConn === 3">
              <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.config_bdd_loading_msg_test_connexion_ko }}</span>
            </div>
          </div>

          <div class="col">
            <div v-if="this.testConnexion.updateFile === 1">
              <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
              <i>&nbsp;{{ this.translate.config_bdd_loading_msg_update_file }}</i>
            </div>
            <div v-else-if="this.testConnexion.updateFile === 2">
              <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.config_bdd_loading_msg_update_file }}</span>
            </div>
            <div v-else-if="this.testConnexion.updateFile === 3">
              <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.config_bdd_loading_msg_update_file }}</span>
            </div>

          </div>

          <div class="col">
            <div class="btn btn-secondary float-end" :class="this.testConnexion.loading ? 'disabled' : ''"
                @click="this.updateConfigBddEnv(this.datas.option_connexion.test_connexion)">
        <span v-if="!this.testConnexion.loading">
          <i class="bi  bi-gear-fill"></i> {{ this.translate.config_bdd_btn_test_config }}
        </span>
              <span v-else>
          <span class="spinner-border spinner-border-sm" aria-hidden="true"></span> {{ this.translate.config_bdd_btn_test_config_loading }}
        </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="this.testConnexion.isConnected" class="mt-3">
      <h1 class="text-body-emphasis">{{ this.translate.create_bdd_h1 }}</h1>
      <p>
        {{ this.translate.create_bdd_description_1 }} <br/>
        {{ this.translate.create_bdd_description_2 }}
      </p>

      <div class="alert alert-secondary">
        <h5 class="alert-heading"><i class="bi bi-info-circle"></i> {{ this.translate.create_bdd_alert_title }}</h5>
        <p>{{ this.translate.create_bdd_alert_text_1 }}</p>
        <ul>
          <li>{{ this.translate.create_bdd_alert_text_2 }} : <b>{{ this.datas.bdd_params.database_schema }}</b></li>
          <li>{{ this.translate.create_bdd_alert_text_3 }} :
            <b v-html="this.datas.bdd_params.database_prefix === '' ? this.translate.create_bdd_alert_text_4 : this.datas.bdd_params.database_prefix + '_'"></b>
          </li>
        </ul>
        <p>
          <span v-html="this.translate.create_bdd_alert_text_5"></span>
        </p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="bi bi-database-add"></i> {{ this.translate.create_bdd_title }}
        </div>
        <div class="card-body">

          <div class="row">

            <div class="col">
              <div class="mb-3">
                <label for="bdd-config-bdd-name" class="form-label">{{ this.translate.create_bdd_input_bdd_name_label }}</label>
                <input type="text" class="form-control" :class="this.isValideInput('valideName')" id="bdd-config-bdd-name"
                    v-model="this.bddConfig.bdd_name" @keyup="this.sanitizeBddName"
                    @change="(event) => this.checkValideField(event,'valideName')">
                <div id="bdd-config-bdd-name-error" class="invalid-feedback">
                  {{ this.translate.create_bdd_input_bdd_name_error }}
                </div>
              </div>
            </div>

            <div class="col">
              <div class="mb-3">
                <label for="bdd-config-bdd-name" class="form-label">{{ this.translate.create_bdd_input_version_label }}</label>
                <input type="text" class="form-control" :class="this.isValideInput('valideVersion')"
                    id="bdd-config-bdd-name" v-model="this.bddConfig.version" @change="(event) => this.checkValideField(event,'valideVersion')">
                <div id="bdd-config-bdd-name-error" class="invalid-feedback">
                  {{ this.translate.create_bdd_input_version_error }}
                </div>
              </div>
            </div>

            <div class="col">
              <div class="mb-3">
                <label for="bdd-config-bdd-name" class="form-label">{{ this.translate.create_bdd_input_charset_label }}</label>
                <select class="form-select" id="bdd-config-bdd-name" v-model="this.bddConfig.charset">
                  <option value="utf8">utf8</option>
                  <option value="utf8mb4">utf8mb4</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer text-body-secondary">
          <div v-if="this.createDatabase.showBtn" class="btn btn-secondary float-end" :class="this.canCreateBdd()" @click="this.createAllDataBdd()">
           <i class="bi bi-plus-square"></i> {{ this.translate.create_bdd_btn_create }}
          </div>

          <div v-if="this.createDatabase.updateFile === 1">
            <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
            <i>&nbsp;{{ this.translate.create_bdd_loading_msg_update_file }}</i>
          </div>
          <div v-else-if="this.createDatabase.updateFile === 2">
            <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_update_file }}</span>
          </div>
          <div v-else-if="this.createDatabase.updateFile === 3">
            <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_update_file }}</span>
          </div>

          <div v-if="this.createDatabase.updateSecret === 1">
            <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
            <i>&nbsp;{{ this.translate.create_bdd_loading_msg_update_secret }}</i>
          </div>
          <div v-else-if="this.createDatabase.updateSecret === 2">
            <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_update_secret_success }}</span>
          </div>
          <div v-else-if="this.createDatabase.updateSecret === 3">
            <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_update_secret_ko }}</span>
          </div>

          <div v-if="this.createDatabase.createBdd === 1">
            <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
            <i>&nbsp;{{ this.translate.create_bdd_loading_msg_create_bdd }}</i>
          </div>
          <div v-else-if="this.createDatabase.createBdd === 2">
            <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_create_bdd_success }}</span>
          </div>
          <div v-else-if="this.createDatabase.createBdd === 3">
            <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_create_bdd_ko }}</span>
          </div>

          <div v-if="this.createDatabase.createTable === 1">
            <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
            <i>&nbsp;{{ this.translate.create_bdd_loading_msg_create_table }}</i>
          </div>
          <div v-else-if="this.createDatabase.createTable === 2">
            <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_create_table_success }}</span>
          </div>
          <div v-else-if="this.createDatabase.createTable === 3">
            <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_create_table_ko }}</span>
          </div>

          <div v-if="this.createDatabase.redirect">
            <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.create_bdd_loading_msg_success }}</span>
          </div>

          <div v-else-if="this.createDatabase.error !== null">
            <span class="text-danger">&emsp;<i class="bi bi-arrow-return-right"> </i> {{ this.createDatabase.error }}</span>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>
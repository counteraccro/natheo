<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import Toast from "../../Components/Global/Toast.vue";

export default {
  name: "Installation-step-one",
  components: {
    Toast

  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    datas: Object
  },
  data() {
    return {
      loading: false,
      testConnexion: {
        isConnected: null,
        loading: false,
        updateFile: 0,
        testConn: 0
      },
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      },
    }
  },
  mounted() {
    this.checkConnexion();
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

      axios.post(this.urls.update_env, {
        'config_key': this.datas.config_key.database_url,
        'config': this.datas.bdd_config,
        'type': type
      }).then((response) => {
        this.testConnexion.updateFile = 2;
        this.checkConnexion();
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
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.testConnexion.testConn = 2;
        this.testConnexion.isConnected = false;
        this.testConnexion.loading = false;
      });
    },

    /**
     * Met à jour le fieldset en fonction de isConnected
     * @return {string}
     */
    isConnectedFieldset() {
      if (this.testConnexion.isConnected === null) {
        return "";
      }

      if (this.testConnexion.isConnected) {
        return "border border-1 border-success";
      } else {
        return "border border-1 border-danger";
      }

    },

    /**
     * Met à jour le legend en fonction de isConnected
     * @return {string}
     */
    isConnectedLegend() {
      if (this.testConnexion.isConnected === null) {
        return '<i class="bi bi-database-gear"></i> ' + this.translate.config_bdd_title;
      }

      if (this.testConnexion.isConnected) {
        return '<span class="text-success"><strong><i class="bi bi-database-check"></i> ' + this.translate.config_bdd_title + '</strong></span>';
      } else {
        return '<span class="text-danger font-weight-bold"><strong><i class="bi bi-database-x"></i> ' + this.translate.config_bdd_title + '</strong></span>';
      }
    },

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },
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

    <div class="d-flex align-items-center pb-3 mb-5 border-bottom">
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

    <fieldset :class="this.isConnectedFieldset()">
      <legend v-html="this.isConnectedLegend()"></legend>

      <div class="row">

        <div class="col">
          <div class="mb-3">
            <label for="bdd-config-type" class="form-label">{{ this.translate.config_bdd_input_type_label }}</label>
            <input type="text" class="form-control" id="bdd-config-type" v-model="this.datas.bdd_config.type" disabled>
          </div>
        </div>
        <div class="col">
          <div class="mb-3">
            <label for="bdd-config-login" class="form-label">{{ this.translate.config_bdd_input_login_label }}</label>
            <input type="text" class="form-control" id="bdd-config-login" v-model="this.datas.bdd_config.login">
          </div>
        </div>
        <div class="col">
          <div class="mb-3">
            <label for="bdd-config-password" class="form-label">{{ this.translate.config_bdd_input_password_label }}</label>
            <input type="text" class="form-control" id="bdd-config-password" v-model="this.datas.bdd_config.password">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <div class="mb-3">
            <label for="bdd-config-password" class="form-label">{{ this.translate.config_bdd_input_ip_label }}</label>
            <input type="text" class="form-control" id="bdd-config-password" v-model="this.datas.bdd_config.ip">
          </div>
        </div>
        <div class="col">
          <div class="mb-3">
            <label for="bdd-config-password" class="form-label">{{ this.translate.config_bdd_input_port_label }}</label>
            <input type="text" class="form-control" id="bdd-config-password" v-model="this.datas.bdd_config.port">
          </div>
        </div>
        <div class="col">
        </div>
      </div>

      <div class="row">
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

          <div v-if="this.testConnexion.testConn === 1">
            <span class="spinner-border spinner-border-sm text-secondary" aria-hidden="true"></span>
            <i>&nbsp;{{ this.translate.config_bdd_loading_msg_test_connexion }}</i>
          </div>
          <div v-else-if="this.testConnexion.testConn === 2">
            <span class="text-success"><i class="bi bi-check-circle-fill"> </i> {{ this.translate.config_bdd_loading_msg_test_connexion }}</span>
          </div>
          <div v-else-if="this.testConnexion.testConn === 3">
            <span class="text-danger"><i class="bi bi-x-circle-fill"> </i> {{ this.translate.config_bdd_loading_msg_test_connexion }}</span>
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


    </fieldset>
  </div>


  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastError'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastError.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>

</template>
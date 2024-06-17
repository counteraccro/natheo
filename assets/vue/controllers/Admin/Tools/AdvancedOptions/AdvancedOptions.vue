<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Options Avancées du CMS
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import Modal from "../../../../Components/Global/Modal.vue";
import SchemaDatabase from "../../../../Components/DatabaseManager/SchemaDatabse.vue";
import SchemaTable from "../../../../Components/DatabaseManager/SchemaTable.vue";
import ListDump from "../../../../Components/DatabaseManager/ListDump.vue";

export default {
  name: "AdvancedOptions",
  components: {
    Modal,
    Toast
  },
  props: {
    urls: Object,
    translate: Object,
    data: Object
  },
  data() {
    return {
      loading: false,
      msgInfoSwitch: '',
      modalTab: {
        modaleConfirmSwitchEnv: false,
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
    //this.loadSchemaDataBase()
  },
  methods: {

    /**
     * Chargement du schema de la base de donnée
     */
    /*loadSchemaDataBase() {
      this.show = 'schemaDatabase';

    },*/

    switchMode(confirm) {
      if (!confirm) {
        this.updateModale('modaleConfirmSwitchEnv', true);
      } else {
        this.updateModale('modaleConfirmSwitchEnv', false);

        this.loading = true;
        this.msgInfoSwitch = this.translate.msg_info_switch;
        axios.get(this.urls.switch_env).then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
          }
        }).catch((error) => {
          console.error(error);
        }).finally(() => {
          this.msgInfoSwitch = this.translate.msg_info_switch_end;
          location.reload();
        });

      }
    },

    /**
     * Défini si on est en environnement de dev ou non
     * @returns {boolean}
     */
    isDevEnv() {
      return this.data.app_env === 'dev';
    },


    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModale(nameModale, state) {
      this.modalTab[nameModale] = state;
    },

    /**
     * Ferme une modale
     * @param nameModale
     */
    closeModal(nameModale) {
      this.updateModale(nameModale, false);
    },
  },
}
</script>

<template>


  <div id="block-advanced-options" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.msgInfoSwitch !== ''" class="alert alert-secondary mb-3">
      {{ this.msgInfoSwitch }}
    </div>

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="card border-secondary">
      <div class="card-header text-bg-secondary">
        {{ this.translate.switch_env_title }}
      </div>
      <div class="card-body">
        <h5 class="card-title">
          <span v-if="this.isDevEnv()">{{ this.translate.switch_env_subtitle_dev }}</span>
          <span v-else>{{ this.translate.switch_env_subtitle_prod }}</span>
        </h5>
        <div v-if="this.isDevEnv()">
          <span class="card-text">{{ this.translate.switch_env_define_dev }}</span>
          <ul>
            <li>{{ this.translate.switch_env_define_dev_1 }}</li>
            <li>{{ this.translate.switch_env_define_dev_2 }}</li>
            <li>{{ this.translate.switch_env_define_dev_3 }}</li>
            <li>{{ this.translate.switch_env_define_dev_4 }}</li>
          </ul>

          <div class="alert alert-danger">
            <i>{{ this.translate.switch_env_define_dev_warning }}</i>
          </div>

        </div>
        <div v-else>
          <span class="card-text"> {{ this.translate.switch_env_define_prod }}</span>
          <ul>
            <li>{{ this.translate.switch_env_define_prod_1 }}</li>
            <li>{{ this.translate.switch_env_define_prod_2 }}</li>
            <li>{{ this.translate.switch_env_define_prod_3 }}</li>
          </ul>
          <div class="alert alert-danger">
            <i>{{ this.translate.switch_env_define_prod_warning }}</i>
          </div>
        </div>
        <div @click="this.switchMode(false)" class="btn btn-secondary float-end">
          <span v-if="!this.isDevEnv()"> {{ this.translate.switch_env_btn_dev }} </span>
          <span v-else> {{ this.translate.switch_env_btn_prod }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- modale confirmation suppression -->
  <modal
      :id="'modaleConfirmSwitchEnv'"
      :show="this.modalTab.modaleConfirmSwitchEnv"
      @close-modal="this.closeModal"
      :optionModalSize="'modal-lg'"
      :option-modal-backdrop="'static'"
      :option-show-close-btn="false">
    <template #title>
      <i class="bi bi-exclamation-circle-fill"></i> {{ this.translate.confirm_modale_env.modale_title }}
    </template>
    <template #body>
      <p class="text-black">{{ this.translate.confirm_modale_env.modale_body_text_1 }}</p>
      <p><i>{{ this.translate.confirm_modale_env.modale_body_text_2 }}</i></p>
    </template>
    <template #footer>
      <div class="btn btn-secondary float-end" @click="this.closeModal('modaleConfirmSwitchEnv')"><i class="bi bi-x-circle-fill"></i> {{ this.translate.confirm_modale_env.modale_btn_undo }}</div>
      <div class="btn btn-secondary float-end" @click="this.switchMode(true)">
        <i class="bi bi-check-circle-fill"></i> {{ this.translate.confirm_modale_env.modale_btn_confirm }}
      </div>
    </template>
  </modal>
  <!-- fin modale confirmation suppression -->
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
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
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
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>

</template>
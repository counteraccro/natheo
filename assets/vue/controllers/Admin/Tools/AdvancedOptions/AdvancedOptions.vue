<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Options Avancées du CMS
 */
import axios from 'axios';
import Toast from '../../../../Components/Global/Toast.vue';
import Modal from '../../../../Components/Global/Modal.vue';
import SchemaDatabase from '../../../../Components/DatabaseManager/SchemaDatabse.vue';
import SchemaTable from '../../../../Components/DatabaseManager/SchemaTable.vue';
import ListDump from '../../../../Components/DatabaseManager/ListDump.vue';

export default {
  name: 'AdvancedOptions',
  components: {
    Modal,
    Toast,
  },
  props: {
    urls: Object,
    translate: Object,
    data: Object,
  },
  data() {
    return {
      loading: false,
      msgInfo: '',
      currentConfirmAction: '',
      urlRedirect: '',
      modalConfirm: {
        title: '',
        text_1: '',
        text_2: '',
        btn_undo: '',
        btn_go: '',
      },

      modalTab: {
        modaleConfirm: false,
      },
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
      },
    };
  },
  mounted() {
    //this.loadSchemaDataBase()
  },
  methods: {
    openConfirmModale(action, confirm) {
      this.currentConfirmAction = action;
      if (!confirm) {
        this.updateModale('modaleConfirm', true);
      } else {
        this.updateModale('modaleConfirm', false);
      }

      switch (action) {
        case 'switchEnv':
          this.switchMode(confirm);
          break;
        case 'resetDatabase':
          this.resetDatabase(confirm);
          break;
        case 'resetData':
          this.resetData(confirm);
          break;
      }
    },

    /**
     * Changement d'env
     */

    switchMode(confirm) {
      if (!confirm) {
        this.modalConfirm.title = this.translate.confirm_modale_env.modale_title;
        this.modalConfirm.text_1 = this.translate.confirm_modale_env.modale_body_text_1;
        this.modalConfirm.text_2 = this.translate.confirm_modale_env.modale_body_text_2;
        this.modalConfirm.btn_go = this.translate.confirm_modale_env.modale_btn_confirm;
        this.modalConfirm.btn_undo = this.translate.confirm_modale_env.modale_btn_undo;
        return;
      }

      this.loading = true;
      this.msgInfo = this.translate.msg_info.switch_env;
      axios
        .get(this.urls.switch_env)
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.msgInfo = this.translate.msg_info.switch_env_end;
          location.reload();
        });
    },

    /**
     * Reset de la base de donnée
     * @param confirm
     */
    resetDatabase(confirm) {
      if (!confirm) {
        this.modalConfirm.title = this.translate.confirm_modale_reset_database.modale_title;
        this.modalConfirm.text_1 = this.translate.confirm_modale_reset_database.modale_body_text_1;
        this.modalConfirm.text_2 = this.translate.confirm_modale_reset_database.modale_body_text_2;
        this.modalConfirm.btn_go = this.translate.confirm_modale_reset_database.modale_btn_confirm;
        this.modalConfirm.btn_undo = this.translate.confirm_modale_reset_database.modale_btn_undo;
        return;
      }

      this.loading = true;
      this.msgInfo = this.translate.msg_info.reset_database;
      axios
        .get(this.urls.reset_database)
        .then((response) => {
          this.urlRedirect = response.data.redirect;
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.msgInfo = this.translate.msg_info.reset_database_end;
          window.location = this.urlRedirect;
        });
    },

    /**
     * Réinstallation des données
     * @param confirm
     */
    resetData(confirm) {
      if (!confirm) {
        this.modalConfirm.title = this.translate.confirm_modale_reset_data.modale_title;
        this.modalConfirm.text_1 = this.translate.confirm_modale_reset_data.modale_body_text_1;
        this.modalConfirm.text_2 = this.translate.confirm_modale_reset_data.modale_body_text_2;
        this.modalConfirm.btn_go = this.translate.confirm_modale_reset_data.modale_btn_confirm;
        this.modalConfirm.btn_undo = this.translate.confirm_modale_reset_data.modale_btn_undo;
        return;
      }

      this.loading = true;
      this.msgInfo = this.translate.msg_info.reset_data;
      axios
        .get(this.urls.reset_data)
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.msgInfo = this.translate.msg_info.reset_data_end;
          location.reload();
        });
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
      this.toasts[nameToast].show = false;
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
};
</script>

<template>
  <div class="card rounded-lg p-6 mb-4 mt-4">
    <div class="border-b-1 border-b-[var(--border-color)] mb-4">
      <h2 class="flex gap-2 text-lg font-bold text-[var(--text-primary)]">
        <svg
          class="icon-lg"
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
            d="M10 3v4a1 1 0 0 1-1 1H5m8 7.5 2.5 2.5M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Zm-5 9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"
          ></path>
        </svg>
        {{ this.translate.switch_env_title }}
      </h2>
      <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">bla bla bla</p>
    </div>

    <span v-if="this.isDevEnv()">{{ this.translate.switch_env_subtitle_dev }}</span>
    <span v-else>{{ this.translate.switch_env_subtitle_prod }}</span>

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
  </div>

  <div id="block-advanced-options" :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.msgInfo !== ''" class="card border-secondary mb-3">
      <div class="card-header text-bg-secondary">
        {{ this.translate.msg_info.title }}
      </div>
      <div class="card-body">
        <div class="spinner-border text-secondary spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        {{ this.msgInfo }}
      </div>
    </div>

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="card border-secondary">
      <div class="card-header text-bg-secondary">
        <i class="bi bi-arrow-left-right"></i> {{ this.translate.switch_env_title }}
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
        <div @click="this.openConfirmModale('switchEnv', false)" class="btn btn-secondary float-end">
          <span v-if="!this.isDevEnv()"> {{ this.translate.switch_env_btn_dev }} </span>
          <span v-else> {{ this.translate.switch_env_btn_prod }}</span>
        </div>
      </div>
    </div>

    <div v-if="this.isDevEnv()">
      <fieldset class="border-1 border-danger mt-3 p-3">
        <legend class="text-danger">
          <i class="bi bi-exclamation-octagon-fill"></i> {{ this.translate.title_danger_zone }}
        </legend>

        <p class="text-danger">
          <b>{{ this.translate.warning_danger_zone }}</b>
        </p>

        <div class="row">
          <div class="col-6">
            <div class="card border-secondary">
              <div class="card-header text-bg-secondary">
                <i class="bi bi-database-fill-down"></i> {{ this.translate.reload_data.title }}
              </div>
              <div class="card-body">
                <p class="text-black">{{ this.translate.reload_data.text_1 }}</p>
                <p class="text-danger">
                  <i>{{ this.translate.reload_data.warning }}</i>
                </p>
                <div class="btn btn-secondary float-end" @click="this.openConfirmModale('resetData', false)">
                  {{ this.translate.reload_data.btn }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card border-secondary">
              <div class="card-header text-bg-secondary">
                <i class="bi bi-database-fill-x"></i> {{ this.translate.reset_database.title }}
              </div>
              <div class="card-body">
                <p class="text-black">{{ this.translate.reset_database.text_1 }}</p>
                <p class="text-danger">
                  <i>{{ this.translate.reset_database.warning }}</i>
                </p>
                <div class="btn btn-secondary float-end" @click="this.openConfirmModale('resetDatabase', false)">
                  {{ this.translate.reset_database.btn }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>

  <!-- modale confirmation suppression -->
  <modal
    :id="'modaleConfirm'"
    :show="this.modalTab.modaleConfirm"
    @close-modal="this.closeModal"
    :optionModalSize="'modal-lg'"
    :option-modal-backdrop="'static'"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-exclamation-circle-fill"></i> {{ this.modalConfirm.title }} </template>
    <template #body>
      <p class="text-black">{{ this.modalConfirm.text_1 }}</p>
      <p>
        <i>{{ this.modalConfirm.text_2 }}</i>
      </p>
    </template>
    <template #footer>
      <div class="btn btn-secondary float-end" @click="this.closeModal('modaleConfirm')">
        <i class="bi bi-x-circle-fill"></i> {{ this.modalConfirm.btn_undo }}
      </div>
      <div class="btn btn-secondary float-end" @click="this.openConfirmModale(this.currentConfirmAction, true)">
        <i class="bi bi-check-circle-fill"></i> {{ this.modalConfirm.btn_go }}
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

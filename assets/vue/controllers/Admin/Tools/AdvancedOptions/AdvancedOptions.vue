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
import AlertWarning from '@/vue/Components/Alert/Warning.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';

export default {
  name: 'AdvancedOptions',
  components: {
    AlertDanger,
    SkeletonText,
    AlertWarning,
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
  <div v-if="this.loading">
    <div class="card rounded-lg p-6 mb-4 mt-4">
      <skeleton-text />
    </div>
  </div>
  <div v-else>
    <div class="card rounded-lg p-6 mb-4 mt-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <h2 class="flex gap-2 text-lg font-bold text-[var(--text-primary)]">
          <svg
            class="icon-lg"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              d="M12.356 3.066a1 1 0 0 0-.712 0l-7 2.666A1 1 0 0 0 4 6.68a17.695 17.695 0 0 0 2.022 7.98 17.405 17.405 0 0 0 5.403 6.158 1 1 0 0 0 1.15 0 17.406 17.406 0 0 0 5.402-6.157A17.694 17.694 0 0 0 20 6.68a1 1 0 0 0-.644-.949l-7-2.666Z"
            />
          </svg>

          {{ this.translate.switch_env_title }}
        </h2>
        <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
          <span v-if="this.isDevEnv()">{{ this.translate.switch_env_subtitle_dev }}</span>
          <span v-else>{{ this.translate.switch_env_subtitle_prod }}</span>
        </p>
      </div>

      <div class="text-sm text-[var(--text-secondary)]">
        <div v-if="this.isDevEnv()">
          <p class="mb-2">{{ this.translate.switch_env_define_dev }}</p>
          <ul class="list-disc list-inside space-y-1 ml-2">
            <li>{{ this.translate.switch_env_define_dev_1 }}</li>
            <li>{{ this.translate.switch_env_define_dev_2 }}</li>
            <li>{{ this.translate.switch_env_define_dev_3 }}</li>
            <li>{{ this.translate.switch_env_define_dev_4 }}</li>
          </ul>

          <alert-warning type="alert-warning-light" :text="this.translate.switch_env_define_dev_warning" class="mt-5" />
        </div>
        <div v-else>
          <p class="mb-2">{{ this.translate.switch_env_define_prod }}</p>
          <ul class="list-disc list-inside space-y-1 ml-2">
            <li>{{ this.translate.switch_env_define_prod_1 }}</li>
            <li>{{ this.translate.switch_env_define_prod_2 }}</li>
            <li>{{ this.translate.switch_env_define_prod_3 }}</li>
          </ul>

          <alert-warning
            type="alert-warning-light"
            :text="this.translate.switch_env_define_prod_warning"
            class="mt-5"
          />
        </div>
      </div>

      <div class="flex flex-row-reverse">
        <div @click="this.openConfirmModale('switchEnv', false)" class="btn btn-sm btn-primary mt-5">
          <svg
            class="icon"
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
              d="m16 10 3-3m0 0-3-3m3 3H5v3m3 4-3 3m0 0 3 3m-3-3h14v-3"
            />
          </svg>

          <span v-if="!this.isDevEnv()"> {{ this.translate.switch_env_btn_dev }} </span>
          <span v-else> {{ this.translate.switch_env_btn_prod }}</span>
        </div>
      </div>
    </div>
  </div>

  <div v-if="this.isDevEnv()">
    <div v-if="this.loading">
      <div class="card rounded-lg p-6 mb-4 mt-4">
        <skeleton-text />
      </div>
    </div>
    <div v-else>
      <div class="card rounded-lg p-6 mb-4 mt-4" style="border: 2px solid var(--btn-danger)">
        <div class="border-b-1 border-b-[var(--border-color)] mb-4">
          <h2 class="flex gap-2 text-lg font-bold text-[var(--btn-danger)]">
            <svg
              class="icon-lg"
              style="color: var(--btn-danger)"
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
                d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
              />
            </svg>

            {{ this.translate.title_danger_zone }}
          </h2>
          <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">{{ this.translate.subtitle_danger_zone }}</p>
        </div>

        <alert-danger type="alert-danger-light" :text="this.translate.warning_danger_zone" />

        <div class="mb-6 pb-6 border-b-1 border-b-[var(--border-color)]">
          <h4 class="text-base font-semibold mb-2 mt-4 text-[var(--text-primary)]">
            {{ this.translate.reload_data.title }}
          </h4>
          <p class="text-sm mb-2 text-[var(--text-secondary)]">{{ this.translate.reload_data.text_1 }}</p>
          <p class="text-sm mb-2 text-[var(--text-secondary)]">
            <i>{{ this.translate.reload_data.warning }}</i>
          </p>

          <div class="flex flex-row-reverse">
            <div class="btn btn-primary btn-sm" @click="this.openConfirmModale('resetData', false)">
              <svg
                class="icon"
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
                  d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"
                />
              </svg>

              {{ this.translate.reload_data.btn }}
            </div>
          </div>
        </div>

        <div>
          <h4 class="text-base font-semibold mb-2 mt-4 text-[var(--text-primary)]">
            {{ this.translate.reset_database.title }}
          </h4>
          <p class="text-sm mb-2 text-[var(--text-secondary)]">{{ this.translate.reset_database.text_1 }}</p>
          <p class="text-sm mb-2 text-[var(--text-secondary)]">
            <i>{{ this.translate.reset_database.warning }}</i>
          </p>

          <div class="flex flex-row-reverse">
            <div class="btn btn-primary btn-sm" @click="this.openConfirmModale('resetDatabase', false)">
              <svg
                class="icon"
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
                  d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"
                />
              </svg>

              {{ this.translate.reset_database.btn }}
            </div>
          </div>
        </div>
      </div>
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
      <p class="text-sm text-[var(--text-secondary)]">{{ this.modalConfirm.text_1 }}</p>
      <p class="text-sm text-[var(--text-secondary)]">
        <i>{{ this.modalConfirm.text_2 }}</i>
      </p>
    </template>
    <template #footer>
      <button
        type="button"
        class="btn btn-primary btn-sm me-2"
        @click="this.openConfirmModale(this.currentConfirmAction, true)"
      >
        <svg
          class="icon"
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
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>
        {{ this.modalConfirm.btn_go }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="this.closeModal('modaleConfirm')">
        <svg
          class="icon"
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
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ this.modalConfirm.btn_undo }}
      </button>
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
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

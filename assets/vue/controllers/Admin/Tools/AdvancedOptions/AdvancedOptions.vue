<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Options Avancées du CMS
 */
import { defineComponent, PropType } from 'vue';
import axios from 'axios';
import Toast from '../../../../Components/Global/Toast.vue';
import Modal from '../../../../Components/Global/Modal.vue';
import AlertWarning from '@/vue/Components/Alert/Warning.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import { Toasts } from '@/ts/Toast/Toast.type';
import type {
  AdvancedOptionsUrls,
  AdvancedOptionsCsrfTokens,
  AdvancedOptionsData,
  AdvancedOptionsTranslations,
  AdvancedOptionsConfirmAction,
  AdvancedOptionsModalConfirm,
  AdvancedOptionsResponse,
} from '@/ts/AdvancedOptions/AdvancedOptions.type';

export default defineComponent({
  name: 'AdvancedOptions',
  components: {
    AlertDanger,
    SkeletonText,
    AlertWarning,
    Modal,
    Toast,
  },
  props: {
    urls: {
      type: Object as PropType<AdvancedOptionsUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<AdvancedOptionsTranslations>,
      required: true,
    },
    data: {
      type: Object as PropType<AdvancedOptionsData>,
      required: true,
    },
    csrfTokens: {
      type: Object as PropType<AdvancedOptionsCsrfTokens>,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      msgInfo: '',
      currentConfirmAction: '' as AdvancedOptionsConfirmAction | '',
      modalConfirm: {
        title: '',
        text_1: '',
        text_2: '',
        btn_undo: '',
        btn_go: '',
      } as AdvancedOptionsModalConfirm,

      modalTab: {
        modaleConfirm: false,
      } as Record<string, boolean>,
      toasts: {
        toastError: {
          show: false,
          msg: '',
        },
      } as Toasts,
    };
  },
  methods: {
    openConfirmModale(action: AdvancedOptionsConfirmAction | '', confirm: boolean) {
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
     * Change le contenu de la modale de confirmation
     * @param translate
     */
    setModalConfirm(translate: AdvancedOptionsTranslations['confirm_modale_env']) {
      this.modalConfirm.title = translate.modale_title;
      this.modalConfirm.text_1 = translate.modale_body_text_1;
      this.modalConfirm.text_2 = translate.modale_body_text_2;
      this.modalConfirm.btn_go = translate.modale_btn_confirm;
      this.modalConfirm.btn_undo = translate.modale_btn_undo;
    },

    /**
     * Exécute une action serveur ; en cas de succès la page est rechargée ou redirigée,
     * en cas d'échec l'écran est rendu et l'erreur affichée
     * @param url
     * @param csrfToken
     * @param msgInfo message affiché pendant l'action
     * @param msgInfoEnd message affiché pendant le rechargement
     */
    executeAction(url: string, csrfToken: string, msgInfo: string, msgInfoEnd: string) {
      this.loading = true;
      this.msgInfo = msgInfo;
      axios
        .post<AdvancedOptionsResponse>(url, {}, { headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then((response) => {
          if (response.data.success !== true) {
            this.showError(response.data.msg);
            return;
          }
          this.msgInfo = msgInfoEnd;
          if (response.data.redirect) {
            window.location.href = response.data.redirect;
          } else {
            location.reload();
          }
        })
        .catch((error) => {
          console.error(error);
          const msg = axios.isAxiosError<AdvancedOptionsResponse>(error) ? error.response?.data?.msg : undefined;
          this.showError(msg);
        });
    },

    /**
     * Affiche une erreur et rend la main à l'utilisateur
     * @param msg
     */
    showError(msg?: string) {
      this.loading = false;
      this.msgInfo = '';
      this.toasts.toastError.msg = msg || this.translate.error_generic;
      this.toasts.toastError.show = true;
    },

    /**
     * Changement d'env
     * @param confirm
     */
    switchMode(confirm: boolean) {
      if (!confirm) {
        this.setModalConfirm(this.translate.confirm_modale_env);
        return;
      }

      this.executeAction(
        this.urls.switch_env,
        this.csrfTokens.switch_env,
        this.translate.msg_info.switch_env,
        this.translate.msg_info.switch_env_end
      );
    },

    /**
     * Reset de la base de donnée
     * @param confirm
     */
    resetDatabase(confirm: boolean) {
      if (!confirm) {
        this.setModalConfirm(this.translate.confirm_modale_reset_database);
        return;
      }

      this.executeAction(
        this.urls.reset_database,
        this.csrfTokens.reset_database,
        this.translate.msg_info.reset_database,
        this.translate.msg_info.reset_database_end
      );
    },

    /**
     * Réinstallation des données
     * @param confirm
     */
    resetData(confirm: boolean) {
      if (!confirm) {
        this.setModalConfirm(this.translate.confirm_modale_reset_data);
        return;
      }

      this.executeAction(
        this.urls.reset_data,
        this.csrfTokens.reset_data,
        this.translate.msg_info.reset_data,
        this.translate.msg_info.reset_data_end
      );
    },

    /**
     * Défini si on est en environnement de dev ou non
     * @returns {boolean}
     */
    isDevEnv(): boolean {
      return this.data.app_env === 'dev';
    },

    /**
     * Défini si les actions de la zone de danger sont autorisées (même règle que le serveur)
     * @returns {boolean}
     */
    isDebug(): boolean {
      return this.data.app_debug;
    },

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast: string) {
      this.toasts[nameToast].show = false;
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModale(nameModale: string, state: boolean) {
      this.modalTab[nameModale] = state;
    },

    /**
     * Ferme une modale
     * @param nameModale
     */
    closeModal(nameModale: string) {
      this.updateModale(nameModale, false);
    },
  },
});
</script>

<template>
  <div v-if="loading">
    <div class="card rounded-lg p-6 mb-4 mt-4">
      <p class="text-sm font-semibold mb-1 text-(--text-primary)">{{ translate.msg_info.title }}</p>
      <p class="text-sm mb-4 text-(--text-secondary)">{{ msgInfo }}</p>
      <skeleton-text />
    </div>
  </div>
  <div v-else>
    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="currentColor"
              viewBox="0 0 24 24"
              style="color: var(--primary)"
            >
              <path
                d="M12.356 3.066a1 1 0 0 0-.712 0l-7 2.666A1 1 0 0 0 4 6.68a17.695 17.695 0 0 0 2.022 7.98 17.405 17.405 0 0 0 5.403 6.158 1 1 0 0 0 1.15 0 17.406 17.406 0 0 0 5.402-6.157A17.694 17.694 0 0 0 20 6.68a1 1 0 0 0-.644-.949l-7-2.666Z"
              />
            </svg>

            {{ translate.switch_env_title }}
          </div>
          <p class="card-subtitle">
            <span v-if="isDevEnv()">{{ translate.switch_env_subtitle_dev }}</span>
            <span v-else>{{ translate.switch_env_subtitle_prod }}</span>
          </p>
        </div>
      </div>
      <div class="p-5">
        <div class="text-sm text-(--text-secondary)">
          <div v-if="isDevEnv()">
            <p class="mb-2">{{ translate.switch_env_define_dev }}</p>
            <ul class="list-disc list-inside space-y-1 ml-2">
              <li>{{ translate.switch_env_define_dev_1 }}</li>
              <li>{{ translate.switch_env_define_dev_2 }}</li>
              <li>{{ translate.switch_env_define_dev_3 }}</li>
              <li>{{ translate.switch_env_define_dev_4 }}</li>
            </ul>

            <alert-warning type="alert-warning-light" :text="translate.switch_env_define_dev_warning" class="mt-5" />
          </div>
          <div v-else>
            <p class="mb-2">{{ translate.switch_env_define_prod }}</p>
            <ul class="list-disc list-inside space-y-1 ml-2">
              <li>{{ translate.switch_env_define_prod_1 }}</li>
              <li>{{ translate.switch_env_define_prod_2 }}</li>
              <li>{{ translate.switch_env_define_prod_3 }}</li>
            </ul>

            <alert-warning type="alert-warning-light" :text="translate.switch_env_define_prod_warning" class="mt-5" />
          </div>
        </div>

        <div class="flex flex-row-reverse">
          <div @click="openConfirmModale('switchEnv', false)" class="btn btn-sm btn-primary mt-5">
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

            <span v-if="!isDevEnv()"> {{ translate.switch_env_btn_dev }} </span>
            <span v-else> {{ translate.switch_env_btn_prod }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="isDebug()">
    <div v-if="loading">
      <div class="card rounded-lg p-6 mb-4 mt-4">
        <skeleton-text />
      </div>
    </div>
    <div v-else>
      <div class="card mb-4" style="border: 2px solid var(--btn-danger)">
        <div class="card-header">
          <div>
            <div class="card-title">
              <svg
                class="card-icon"
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

              <span style="color: var(--btn-danger)">
                {{ translate.title_danger_zone }}
              </span>
            </div>
            <p class="card-subtitle">
              {{ translate.subtitle_danger_zone }}
            </p>
          </div>
        </div>
        <div class="p-5">
          <alert-danger type="alert-danger-light" :text="translate.warning_danger_zone" />

          <div class="mb-6 pb-6 border-b-1 border-b-(--border-color)">
            <h4 class="text-base font-semibold mb-2 mt-4 text-(--text-primary)">
              {{ translate.reload_data.title }}
            </h4>
            <p class="text-sm mb-2 text-(--text-secondary)">{{ translate.reload_data.text_1 }}</p>
            <p class="text-sm mb-2 text-(--text-secondary)">
              <i>{{ translate.reload_data.warning }}</i>
            </p>

            <div class="flex flex-row-reverse">
              <div class="btn btn-primary btn-sm" @click="openConfirmModale('resetData', false)">
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

                {{ translate.reload_data.btn }}
              </div>
            </div>
          </div>

          <div>
            <h4 class="text-base font-semibold mb-2 mt-4 text-(--text-primary)">
              {{ translate.reset_database.title }}
            </h4>
            <p class="text-sm mb-2 text-(--text-secondary)">{{ translate.reset_database.text_1 }}</p>
            <p class="text-sm mb-2 text-(--text-secondary)">
              <i>{{ translate.reset_database.warning }}</i>
            </p>

            <div class="flex flex-row-reverse">
              <div class="btn btn-primary btn-sm" @click="openConfirmModale('resetDatabase', false)">
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

                {{ translate.reset_database.btn }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- modale confirmation suppression -->
  <modal
    :id="'modaleConfirm'"
    :show="modalTab.modaleConfirm"
    @close-modal="closeModal"
    :optionModalSize="'modal-lg'"
    :option-modal-backdrop="'static'"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-exclamation-circle-fill"></i> {{ modalConfirm.title }} </template>
    <template #body>
      <p class="text-sm text-(--text-secondary)">{{ modalConfirm.text_1 }}</p>
      <p class="text-sm text-(--text-secondary)">
        <i>{{ modalConfirm.text_2 }}</i>
      </p>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="openConfirmModale(currentConfirmAction, true)">
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
        {{ modalConfirm.btn_go }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="closeModal('modaleConfirm')">
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

        {{ modalConfirm.btn_undo }}
      </button>
    </template>
  </modal>
  <!-- fin modale confirmation suppression -->
  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="toasts.toastError.show"
      @close-toast="closeToast"
    >
      <template #body>
        <div class="whitespace-pre-line">{{ toasts.toastError.msg }}</div>
      </template>
    </toast>
  </div>
</template>

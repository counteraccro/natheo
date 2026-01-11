<script>
import axios from 'axios';
import Toast from '../../../Components/Global/Toast.vue';
import { copyToClipboard } from '@/utils/copyToClipboard';
import Modal from '../../../Components/Global/Modal.vue';
import { emitter } from '@/utils/useEvent';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';

export default {
  name: 'ApiToken',
  components: {
    SkeletonForm,
    Modal,
    Toast,
  },
  props: {
    translate: Object,
    urls: Object,
    pApiToken: Object,
    datas: Object,
  },
  data() {
    return {
      loading: false,
      apiToken: this.pApiToken,
      showModalApiTokenConfirm: false,
      showModalApiTokenDelete: false,
      canSave: true,
      validation: {
        name: {
          isValide: true,
          msg: '',
        },
        token: {
          isValide: true,
          msg: '',
        },
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
  mounted() {},
  methods: {
    /**
     * Génère un token
     */
    generateToken() {
      this.loading = true;
      axios
        .get(this.urls.generate_token)
        .then((response) => {
          this.apiToken.token = response.data.token;
          this.toasts.toastSuccess.show = true;
          this.toasts.toastSuccess.msg = this.translate.generate_token_success;
          this.validation.token.isValide = true;
          this.validation.token.msg = '';
          this.isAllValidate();
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => (this.loading = false));
    },

    /**
     * Permet de copier coller le token
     */
    copyToken() {
      copyToClipboard(this.apiToken.token).then(() => {
        this.toasts.toastSuccess.show = true;
        this.toasts.toastSuccess.msg = this.translate.token_copy_success;
      });
    },

    /**
     * Sauvegarde du token
     * @param isConfirm
     * @return {boolean}
     */
    saveToken(isConfirm) {
      if (!isConfirm) {
        this.showModalApiTokenConfirm = true;
        return false;
      }

      this.showModalApiTokenConfirm = false;
      this.verifField('name', this.apiToken.name);
      this.verifField('token', this.apiToken.token);

      this.isAllValidate();

      if (!this.canSave) {
        return false;
      }

      this.loading = true;
      axios
        .post(this.urls.save_api_token, {
          apiToken: this.apiToken,
        })
        .then((response) => {
          if (response.data.success) {
            this.toasts.toastSuccess.show = true;
            this.toasts.toastSuccess.msg = response.data.msg;

            if (response.data.redirect !== '') {
              setTimeout(() => {
                window.location.replace(response.data.redirect);
              }, 1500);
            } else {
              this.loading = false;
            }
          } else {
            this.toasts.toastError.show = true;
            this.toasts.toastError.msg = response.data.msg;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          emitter.emit('reset-check-confirm');
        });
    },

    /**
     * Supprime le token
     */
    deleteToken() {
      this.loading = true;
      this.showModalApiTokenDelete = false;

      axios
        .delete(this.urls.delete_api_token)
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
          setTimeout(() => {
            window.location = this.urls.index_api_token;
          }, 1500);
        });
    },

    /**
     * Validation des champs
     * @param name
     * @param value
     */
    verifField(name, value) {
      let isValide = true;
      let msg = '';

      if (value === '' || value === null) {
        isValide = false;
        msg = this.translate[name + '_error'];
      }
      this.validation[name].isValide = isValide;
      this.validation[name].msg = msg;

      this.isAllValidate();
    },

    /**
     * Vérifie si on peut sauvegarder un token
     */
    isAllValidate() {
      this.canSave = true;
      for (let key in this.validation) {
        if (!this.validation[key].isValide) {
          this.canSave = false;
        }
      }
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },

    /**
     * Ferme la modale
     */
    hideModal() {
      this.showModalApiTokenConfirm = false;
      this.showModalApiTokenDelete = false;
    },
  },
};
</script>

<template>
  <div class="card rounded-lg p-6 mb-4 mt-4">
    <div v-if="this.loading">
      <skeleton-form />
    </div>
    <div v-else>
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <h2 class="text-lg font-bold text-[var(--text-primary)]">
          <span v-if="this.apiToken.id === null">
            {{ this.translate.title_add }}
          </span>
          <span v-else>
            {{ this.translate.title_edit }}
          </span>
        </h2>

        <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">
          <span v-if="this.apiToken.id === null">
            {{ this.translate.description_add }}
          </span>
          <span v-else>
            {{ this.translate.description_edit }}
          </span>
        </p>
      </div>

      <div class="form-group">
        <label for="api-token-name" class="form-label required">{{ this.translate.title_label }}</label>
        <input
          type="text"
          class="form-input"
          :class="this.validation.name.isValide ? '' : 'is-invalid'"
          :placeholder="this.translate.title_placeholder"
          id="api-token-name"
          v-model="this.apiToken.name"
          @change="this.verifField('name', this.apiToken.name)"
        />
        <div class="form-text text-error">{{ this.validation.name.msg }}</div>
        <div class="form-text">{{ this.translate.title_help }}</div>
      </div>

      <div class="form-group">
        <label for="api-token-comment" class="form-label">{{ this.translate.comment_label }}</label>
        <textarea
          type="text"
          class="form-input"
          :placeholder="this.translate.comment_placeholder"
          id="api-token-comment"
          v-model="this.apiToken.comment"
        ></textarea>
        <div class="form-text">{{ this.translate.comment_help }}</div>
      </div>

      <label for="token" class="form-label">{{ this.translate.token_label }}</label>
      <div class="input-button-group">
        <input
          type="text"
          class="form-input"
          id="token"
          :class="this.validation.token.isValide ? '' : 'is-invalid'"
          v-model="this.apiToken.token"
          disabled
        />
        <button class="btn btn-primary btn-sm" @click="this.generateToken()">
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          {{ this.translate.btn_new_token }}
        </button>
        <button v-if="this.apiToken.id !== null" class="btn btn-dark btn-sm" type="button" @click="this.copyToken()">
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
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 8v3a1 1 0 0 1-1 1H5m11 4h2a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1h-7a1 1 0 0 0-1 1v1m4 3v10a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-7.13a1 1 0 0 1 .24-.65L7.7 8.35A1 1 0 0 1 8.46 8H13a1 1 0 0 1 1 1Z"
            />
          </svg>

          {{ this.translate.btn_copy_past }}
        </button>
      </div>
      <div class="form-text text-error">{{ this.validation.token.msg }}</div>
      <div class="form-text">
        <span v-if="this.apiToken.id !== null">
          {{ this.translate.input_token_help }}
        </span>
        <span v-else> {{ this.translate.input_token_help_add }} </span>
      </div>

      <div class="form-group mt-3">
        <label for="roles" class="form-label">{{ this.translate.select_label_role }}</label>
        <select class="form-input" id="roles" v-model="this.apiToken.roles[0]">
          <option v-for="(role, key) in this.datas.roles" :value="key">{{ role }}</option>
        </select>
      </div>

      <div class="alert alert-info-bordered">
        <svg class="alert-icon" style="color: var(--alert-info)" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
            clip-rule="evenodd"
          ></path>
        </svg>
        <div class="alert-content">
          <div class="alert-message">
            <div>{{ this.translate.help_role }}</div>
            <ul class="list-disc list-inside mt-1">
              <li>{{ this.translate.help_role_read }}</li>
              <li>{{ this.translate.help_role_write }}</li>
              <li>{{ this.translate.help_role_admin }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 pt-4 mt-5 flex-row-reverse">
        <button
          v-if="this.apiToken.id !== null"
          type="button"
          class="btn btn-sm btn-danger"
          @click="this.showModalApiTokenDelete = true"
        >
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
          {{ this.translate.btn_delete_token }}
        </button>
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.history.back()">
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

          {{ this.translate.btn_cancel_edit_token }}
        </button>
        <button
          class="btn btn-sm btn-primary"
          :disabled="!canSave"
          @click="this.apiToken.id === null ? this.saveToken(true) : this.saveToken(false)"
        >
          <svg v-if="this.apiToken.id === null" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <svg
            v-else
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-width="2"
              d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"
            ></path>
          </svg>
          <span v-if="this.apiToken.id === null">
            {{ this.translate.btn_save_token_api }}
          </span>
          <span v-else>
            {{ this.translate.btn_edit_token_api }}
          </span>
        </button>
      </div>
    </div>
  </div>

  <modal
    :id="'confirm-edit-api-token'"
    :show="this.showModalApiTokenConfirm"
    @close-modal="this.hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ this.translate.modale_title_confirm_edit }} </template>
    <template #body>
      <div v-html="this.translate.modale_title_confirm_text"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="this.saveToken(true)">
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
        {{ translate.modale_title_confirm_btn_ok }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="this.hideModal()">
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

        {{ translate.modale_title_confirm_btn_ko }}
      </button>
    </template>
  </modal>

  <modal
    :id="'confirm-delete-api-token'"
    :show="this.showModalApiTokenDelete"
    @close-modal="this.hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ this.translate.modale_title_confirm_delete }} </template>
    <template #body>
      <div v-html="this.translate.modale_title_confirm_delete_text"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="this.deleteToken()">
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
        {{ translate.modale_title_confirm_btn_ok }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="this.hideModal()">
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

        {{ translate.modale_title_confirm_btn_ko }}
      </button>
    </template>
  </modal>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="this.toasts.toastSuccess.show"
      @close-toast="this.closeToast('toastSuccess')"
    >
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="this.toasts.toastError.show"
      @close-toast="this.closeToast('toastError')"
    >
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

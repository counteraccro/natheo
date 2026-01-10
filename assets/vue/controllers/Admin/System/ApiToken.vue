<script>
import axios from 'axios';
import Toast from '../../../Components/Global/Toast.vue';
import { copyToClipboard } from '../../../../utils/copyToClipboard';
import Modal from '../../../Components/Global/Modal.vue';
import { emitter } from '../../../../utils/useEvent';
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
      canSave: false,
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
  mounted() {
    if (this.apiToken.id !== null) {
      this.canSave = true;
    }

    if (this.apiToken.token === null) {
      this.validation.token.isValide = false;
      this.validation.token.msg = this.translate.input_token_error;
    }
    if (this.apiToken.name === null) {
      this.verifField('name', '');
    }
  },
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
              window.location.replace(response.data.redirect);
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
          this.loading = false;
          emitter.emit('reset-check-confirm');
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
      if (value === '') {
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
    </div>
  </div>

  <div :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loading }}</span>
      </div>
    </div>

    <div class="card rounded-lg p-6 mb-4 mt-4">
      <div class="card-header text-bg-secondary">
        <span v-if="this.apiToken.id === null">
          {{ this.translate.title_add }}
        </span>
        <span v-else>
          {{ this.translate.title_edit }}
        </span>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="api-token-name" class="form-label">{{ this.translate.title_label }}</label>
          <input
            type="text"
            class="form-control"
            :class="this.validation.name.isValide ? '' : 'is-invalid'"
            :placeholder="this.translate.title_placeholder"
            id="api-token-name"
            v-model="this.apiToken.name"
            @change="this.verifField('name', this.apiToken.name)"
          />
          <div class="invalid-feedback">{{ this.validation.name.msg }}</div>
          <div class="form-text">{{ this.translate.title_help }}</div>
        </div>

        <div class="mb-3">
          <label for="api-token-name" class="form-label">{{ this.translate.comment_label }}</label>
          <textarea
            type="text"
            class="form-control"
            :placeholder="this.translate.comment_placeholder"
            id="api-token-name"
            v-model="this.apiToken.comment"
          ></textarea>
          <div class="form-text">{{ this.translate.comment_help }}</div>
        </div>

        <div class="mb-3">
          <div v-if="this.apiToken.id === null">
            <div class="input-group mb-3">
              <input
                type="text"
                class="form-control"
                id="token"
                :class="this.validation.token.isValide ? '' : 'is-invalid'"
                v-model="this.apiToken.token"
                disabled
              />
              <button class="btn btn-secondary" type="button" @click="this.generateToken()">
                <i class="bi bi-gear"></i> {{ this.translate.btn_new_token }}
              </button>
              <div class="invalid-feedback">{{ this.validation.token.msg }}</div>
            </div>
          </div>

          <div v-else>
            <div class="input-group">
              <input
                type="text"
                class="form-control"
                :class="this.validation.token.isValide ? '' : 'is-invalid'"
                id="token"
                v-model="this.apiToken.token"
                disabled
              />
              <button class="btn btn-secondary" type="button" @click="this.generateToken()">
                <i class="bi bi-gear"></i> {{ this.translate.btn_new_token }}
              </button>
              <button class="btn btn-secondary" type="button" @click="this.copyToken()">
                <i class="bi bi-copy"></i> {{ this.translate.btn_copy_past }}
              </button>
              <div class="invalid-feedback">{{ this.validation.token.msg }}</div>
            </div>
            <div class="form-text">
              {{ this.translate.input_token_help }}
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <label for="roles" class="form-label">{{ this.translate.select_label_role }}</label>
            <select class="form-select" id="roles" v-model="this.apiToken.roles[0]">
              <option v-for="(role, key) in this.datas.roles" :value="key">{{ role }}</option>
            </select>
          </div>
          <div class="col">
            <div>{{ this.translate.help_role }}</div>
            <ul>
              <li>{{ this.translate.help_role_read }}</li>
              <li>{{ this.translate.help_role_write }}</li>
              <li>{{ this.translate.help_role_admin }}</li>
            </ul>
          </div>
        </div>

        <div class="float-end">
          <div
            v-if="this.apiToken.id === null"
            class="btn btn-secondary"
            :class="!this.canSave ? 'disabled' : ''"
            @click="this.saveToken(true)"
          >
            {{ this.translate.btn_save_token_api }}
          </div>
          <div v-else class="btn btn-secondary" :class="!this.canSave ? 'disabled' : ''" @click="this.saveToken(false)">
            <i class="bi bi-pencil-square"></i> {{ this.translate.btn_edit_token_api }}
          </div>
        </div>
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
      <button type="button" class="btn btn-primary" @click="this.saveToken(true)">
        <i class="bi bi-check2-circle"></i> {{ translate.modale_title_confirm_btn_ok }}
      </button>
      <button type="button" class="btn btn-secondary" @click="this.hideModal()">
        <i class="bi bi-x-circle"></i> {{ translate.modale_title_confirm_btn_ko }}
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

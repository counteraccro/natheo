<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Formulaire de création / édition d'un token API
 */

import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import Toast from '@/vue/Components/Global/Toast.vue';
import Modal from '@/vue/Components/Global/Modal.vue';
import { copyToClipboard } from '@/utils/copyToClipboard';
import { emitter } from '@/utils/useEvent';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';
import AlertWarning from '@/vue/Components/Alert/Warning.vue';
import type { Toasts } from '@/ts/Toast/Toast.type';
import type {
  ApiToken,
  ApiTokenAjaxResponse,
  ApiTokenCsrfTokens,
  ApiTokenDatas,
  ApiTokenSaveResponse,
  ApiTokenTranslate,
  ApiTokenUrls,
  ApiTokenValidation,
} from '@/ts/ApiToken/ApiToken.type';

export default defineComponent({
  name: 'ApiToken',
  components: {
    AlertWarning,
    SkeletonForm,
    Modal,
    Toast,
  },
  props: {
    translate: { type: Object as PropType<ApiTokenTranslate>, required: true },
    urls: { type: Object as PropType<ApiTokenUrls>, required: true },
    pApiToken: { type: Object as PropType<ApiToken>, required: true },
    datas: { type: Object as PropType<ApiTokenDatas>, required: true },
    csrfTokens: { type: Object as PropType<ApiTokenCsrfTokens>, required: true },
  },
  data() {
    return {
      loading: false as boolean,
      apiToken: this.pApiToken as ApiToken,
      // Token en clair, uniquement après création ou régénération
      plainToken: '' as string,
      showModalApiTokenConfirm: false as boolean,
      showModalApiTokenDelete: false as boolean,
      showModalApiTokenRegenerate: false as boolean,
      canSave: true as boolean,
      validation: {
        name: {
          isValide: true,
          msg: '',
        },
      } as ApiTokenValidation,
      toasts: {
        toastSuccess: { show: false, msg: '' },
        toastError: { show: false, msg: '' },
      } as Toasts,
    };
  },
  methods: {
    /**
     * Régénère le token, l'ancien est immédiatement invalidé
     */
    regenerateToken(): void {
      this.showModalApiTokenRegenerate = false;
      this.loading = true;
      axios
        .put<ApiTokenSaveResponse>(this.urls.regenerate_token, undefined, {
          headers: { 'X-CSRF-TOKEN': this.csrfTokens.regenerate },
        })
        .then((response) => {
          this.showResponseToast(response.data);
          if (response.data.success) {
            this.plainToken = response.data.token;
          }
        })
        .catch((error: unknown) => {
          console.error(error);
          this.showErrorFromResponse(error);
        })
        .finally(() => (this.loading = false));
    },

    /**
     * Permet de copier coller le token
     */
    copyToken(): void {
      copyToClipboard(this.plainToken).then(() => {
        this.toasts.toastSuccess.show = true;
        this.toasts.toastSuccess.msg = this.translate.token_copy_success;
      });
    },

    /**
     * Sauvegarde du token
     * @param isConfirm
     */
    saveToken(isConfirm: boolean): void {
      if (!isConfirm) {
        this.showModalApiTokenConfirm = true;
        return;
      }

      this.showModalApiTokenConfirm = false;
      this.verifField('name', this.apiToken.name);

      if (!this.canSave) {
        return;
      }

      this.loading = true;
      axios
        .post<ApiTokenSaveResponse>(
          this.urls.save_api_token,
          {
            apiToken: this.apiToken,
          },
          { headers: { 'X-CSRF-TOKEN': this.csrfTokens.save } }
        )
        .then((response) => {
          this.showResponseToast(response.data);
          if (response.data.success && response.data.token !== '') {
            this.plainToken = response.data.token;
          }
        })
        .catch((error: unknown) => {
          console.error(error);
          this.showErrorFromResponse(error);
        })
        .finally(() => {
          this.loading = false;
          emitter.emit('reset-check-confirm');
        });
    },

    /**
     * Supprime le token
     */
    deleteToken(): void {
      this.loading = true;
      this.showModalApiTokenDelete = false;

      axios
        .delete<ApiTokenAjaxResponse>(this.urls.delete_api_token, {
          headers: { 'X-CSRF-TOKEN': this.csrfTokens.delete },
        })
        .then((response) => {
          this.showResponseToast(response.data);
          if (response.data.success) {
            setTimeout(() => {
              window.location.href = this.urls.index_api_token;
            }, 1500);
          }
        })
        .catch((error: unknown) => {
          console.error(error);
          this.showErrorFromResponse(error);
        })
        .finally(() => (this.loading = false));
    },

    /**
     * Affiche le toast de succès ou d'erreur en fonction de la réponse ajax
     * @param data
     */
    showResponseToast(data: ApiTokenAjaxResponse): void {
      const toast = data.success ? this.toasts.toastSuccess : this.toasts.toastError;
      toast.msg = data.msg;
      toast.show = true;
    },

    /**
     * Affiche le message d'erreur d'une réponse HTTP en erreur (CSRF invalide, token introuvable...)
     * @param error
     */
    showErrorFromResponse(error: unknown): void {
      if (axios.isAxiosError<ApiTokenAjaxResponse>(error) && error.response?.data?.msg) {
        this.showResponseToast(error.response.data);
      }
    },

    /**
     * Validation des champs
     * @param name
     * @param value
     */
    verifField(name: keyof ApiTokenValidation, value: string | null | undefined): void {
      let isValide = true;
      let msg = '';

      if (value === '' || value === null || value === undefined) {
        isValide = false;
        msg = this.translate[`${name}_error`];
      }
      this.validation[name].isValide = isValide;
      this.validation[name].msg = msg;

      this.isAllValidate();
    },

    /**
     * Vérifie si on peut sauvegarder un token
     */
    isAllValidate(): void {
      this.canSave = Object.values(this.validation).every((field) => field.isValide);
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },

    /**
     * Ferme la modale
     */
    hideModal(): void {
      this.showModalApiTokenConfirm = false;
      this.showModalApiTokenDelete = false;
      this.showModalApiTokenRegenerate = false;
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div v-if="loading">
      <skeleton-form />
    </div>
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg
            class="card-icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
            style="color: var(--primary)"
          >
            <path
              fill="currentColor"
              d="M6.94318 11h-.85227l.96023-2.90909h1.07954L9.09091 11h-.85227l-.63637-2.10795h-.02272L6.94318 11Zm-.15909-1.14773h1.60227v.59093H6.78409v-.59093ZM9.37109 11V8.09091h1.25571c.2159 0 .4048.04261.5667.12784.162.08523.2879.20502.3779.35937.0899.15436.1349.33476.1349.5412 0 .20833-.0464.38873-.1392.54119-.0918.15246-.2211.26989-.3878.35229-.1657.0824-.3593.1236-.5809.1236h-.75003v-.61367h.59093c.0928 0 .1719-.0161.2372-.0483.0663-.03314.1169-.08002.152-.14062.036-.06061.054-.13211.054-.21449 0-.08334-.018-.15436-.054-.21307-.0351-.05966-.0857-.10511-.152-.13636-.0653-.0322-.1444-.0483-.2372-.0483h-.2784V11h-.78981Zm3.41481-2.90909V11h-.7898V8.09091h.7898Z"
            />
            <path
              stroke="currentColor"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
            />
          </svg>
          <span v-if="apiToken.id === null">
            {{ translate.title_add }}
          </span>
          <span v-else>
            {{ translate.title_edit }}
          </span>
        </div>
        <p class="card-subtitle">
          <span v-if="apiToken.id === null">
            {{ translate.description_add }}
          </span>
          <span v-else>
            {{ translate.description_edit }}
          </span>
        </p>
      </div>
    </div>
    <div class="p-5">
      <div class="form-group">
        <label for="api-token-name" class="form-label required">{{ translate.title_label }}</label>
        <input
          type="text"
          class="form-input"
          :class="validation.name.isValide ? '' : 'is-invalid'"
          :placeholder="translate.title_placeholder"
          id="api-token-name"
          v-model="apiToken.name"
          @change="verifField('name', apiToken.name)"
        />
        <div class="form-text text-error">{{ validation.name.msg }}</div>
        <div class="form-text">{{ translate.title_help }}</div>
      </div>

      <div class="form-group">
        <label for="api-token-comment" class="form-label">{{ translate.comment_label }}</label>
        <textarea
          type="text"
          class="form-input"
          :placeholder="translate.comment_placeholder"
          id="api-token-comment"
          v-model="apiToken.comment"
        ></textarea>
        <div class="form-text">{{ translate.comment_help }}</div>
      </div>

      <label for="token" class="form-label">{{ translate.token_label }}</label>
      <div v-if="plainToken !== ''">
        <div class="input-button-group">
          <input type="text" class="form-input" id="token" :value="plainToken" readonly />
          <button class="btn btn-dark btn-sm" type="button" @click="copyToken()">
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
            {{ translate.btn_copy_past }}
          </button>
        </div>
        <alert-warning type="alert-warning-light" :text="translate.token_once_warning" class="mt-3" />
      </div>
      <div v-else-if="apiToken.id !== null">
        <div class="form-text">{{ translate.token_hidden }}</div>
        <button class="btn btn-primary btn-sm mt-2" type="button" @click="showModalApiTokenRegenerate = true">
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            ></path>
          </svg>
          {{ translate.btn_regenerate_token }}
        </button>
      </div>
      <div v-else class="form-text">{{ translate.input_token_help_add }}</div>

      <div class="form-group mt-3">
        <label for="api-token-expires-at" class="form-label">{{ translate.expires_at_label }}</label>
        <input type="date" class="form-input" id="api-token-expires-at" v-model="apiToken.expiresAt" />
        <div class="form-text">{{ translate.expires_at_help }}</div>
      </div>

      <div v-if="apiToken.id !== null" class="form-group">
        <span class="form-label">{{ translate.last_used_at_label }}</span>
        <div class="form-text">{{ apiToken.lastUsedAt ?? translate.last_used_at_never }}</div>
      </div>

      <div class="form-group mt-3">
        <label for="roles" class="form-label">{{ translate.select_label_role }}</label>
        <select class="form-input" id="roles" v-model="apiToken.roles[0]">
          <option v-for="(role, key) in datas.roles" :value="key">{{ role }}</option>
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
            <div>{{ translate.help_role }}</div>
            <ul class="list-disc list-inside mt-1">
              <li>{{ translate.help_role_read }}</li>
              <li>{{ translate.help_role_write }}</li>
              <li>{{ translate.help_role_admin }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 pt-4 mt-5 flex-row-reverse">
        <button
          v-if="apiToken.id !== null"
          type="button"
          class="btn btn-sm btn-danger"
          @click="showModalApiTokenDelete = true"
        >
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
          {{ translate.btn_delete_token }}
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

          {{ translate.btn_cancel_edit_token }}
        </button>
        <a v-if="apiToken.id === null && plainToken !== ''" :href="urls.index_api_token" class="btn btn-sm btn-primary">
          {{ translate.btn_back_list }}
        </a>
        <button
          v-else
          class="btn btn-sm btn-primary"
          :disabled="!canSave"
          @click="apiToken.id === null ? saveToken(true) : saveToken(false)"
        >
          <svg v-if="apiToken.id === null" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <span v-if="apiToken.id === null">
            {{ translate.btn_save_token_api }}
          </span>
          <span v-else>
            {{ translate.btn_edit_token_api }}
          </span>
        </button>
      </div>
    </div>
  </div>

  <modal
    :id="'confirm-edit-api-token'"
    :show="showModalApiTokenConfirm"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.modale_title_confirm_edit }} </template>
    <template #body>
      <div v-html="translate.modale_title_confirm_text"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="saveToken(true)">
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
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
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
    :show="showModalApiTokenDelete"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.modale_title_confirm_delete }} </template>
    <template #body>
      <div v-html="translate.modale_title_confirm_delete_text"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="deleteToken()">
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
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
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
    :id="'confirm-regenerate-api-token'"
    :show="showModalApiTokenRegenerate"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.modale_title_confirm_regenerate }} </template>
    <template #body>
      <div v-html="translate.modale_title_confirm_regenerate_text"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="regenerateToken()">
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
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
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
    <toast :id="'toastSuccess'" :show="toasts.toastSuccess.show" @close-toast="closeToast('toastSuccess')">
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.toastError.show" @close-toast="closeToast('toastError')">
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>
</template>

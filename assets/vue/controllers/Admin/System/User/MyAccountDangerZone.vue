<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet de générer les events pour la danger zone
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import type {
  MyAccountDangerZoneCsrfTokens,
  MyAccountDangerZoneModal,
  MyAccountDangerZoneTranslate,
} from '@/ts/User/MyAccountDangerZone.type';
import Modal from '@/vue/Components/Global/Modal.vue';
import { toastRequestError } from '@/vue/Composables/Global/toast';
import { withCsrf } from '@/vue/Composables/Admin/csrf';

export default defineComponent({
  name: 'MyAccountDangerZone',
  components: {
    Modal,
  },
  props: {
    url_disabled: { type: String, required: true },
    url_delete: { type: String, required: true },
    translate: { type: Object as PropType<MyAccountDangerZoneTranslate>, required: true },
    // Valeurs des options système OS_ALLOW_DELETE_DATA et OS_REPLACE_DELETE_USER ('1' / '0')
    can_delete: { type: String, required: true },
    can_replace: { type: String, required: true },
    csrf_tokens: { type: Object as PropType<MyAccountDangerZoneCsrfTokens>, required: true },
  },
  data() {
    return {
      confirm_disabled: false,
      msg_return: this.translate.loading,
      url_reload: '',
      confirm_delete: false,
      modal: {
        disabled: false,
        delete: false,
      },
    };
  },

  methods: {
    /**
     * Action pour le delete
     */
    deleteAccount(confirm: boolean): void {
      this.confirm_delete = confirm;
      if (!confirm) {
        this.showModal('delete');
      } else {
        axios
          .post(this.url_delete, {}, withCsrf(this.csrf_tokens.delete))
          .then((response) => {
            this.msg_return = response.data.msg;
            this.url_reload = response.data.redirect;
          })
          .catch((error) => {
            toastRequestError(error);
          })
          .finally(() => {
            if (this.url_reload !== '') {
              setTimeout(() => {
                document.location.href = this.url_reload;
              }, 3000);
            }
          });
      }
    },

    /**
     * Action pour désactiver le compte
     */
    disabled(confirm: boolean): void {
      this.confirm_disabled = confirm;
      if (!confirm) {
        this.showModal('disabled');
      } else {
        axios
          .post(this.url_disabled, {}, withCsrf(this.csrf_tokens.disabled))
          .then((response) => {
            this.msg_return = response.data.msg;
            this.url_reload = response.data.redirect;
          })
          .catch((error) => {
            toastRequestError(error);
          })
          .finally(() => {
            if (this.url_reload !== '') {
              setTimeout(() => {
                document.location.href = this.url_reload;
              }, 3000);
            }
          });
      }
    },

    isDelete(): boolean {
      return this.can_delete === '1' && this.can_replace === '0';
    },

    isReplace(): boolean {
      return this.can_delete === '1' && this.can_replace === '1';
    },

    /**
     * Affichage la modale
     */
    showModal(modal: MyAccountDangerZoneModal): void {
      this.modal[modal] = true;
    },

    /**
     * Ferme la modale
     */
    hideModal(modal: MyAccountDangerZoneModal): void {
      this.modal[modal] = false;
    },

    hideDisabledModale(): void {
      this.hideModal('disabled');
    },

    hideDeleteModale(): void {
      this.hideModal('delete');
    },
  },
});
</script>

<template>
  <div class="space-y-4">
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-lg bg-alert-danger-bg border border-alert-danger-border"
    >
      <div>
        <h3 class="font-semibold mb-1 text-alert-danger-text">{{ translate.disabled_title }}</h3>
        <p class="text-sm text-alert-danger-text" style="opacity: 0.8">
          {{ translate.disabled_description }}
        </p>
      </div>
      <button class="btn btn-outline-dark btn-sm whitespace-nowrap" @click="disabled(false)">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
          />
        </svg>
        {{ translate.btn_disabled }}
      </button>
    </div>

    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-lg bg-alert-danger-bg border border-alert-danger-border"
    >
      <div>
        <h3 class="font-semibold mb-1 text-alert-danger-text">
          <span v-if="isDelete()">{{ translate.delete_title }}</span>
          <span v-if="isReplace()">{{ translate.replace_title }}</span>
        </h3>
        <p class="text-sm text-alert-danger-text" style="opacity: 0.8">
          <span v-if="isDelete()">{{ translate.delete_description }}</span>
          <span v-if="isReplace()">{{ translate.replace_description }}</span>
        </p>
      </div>
      <button v-if="isDelete()" @click="deleteAccount(false)" class="btn btn-danger btn-sm whitespace-nowrap">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          ></path>
        </svg>
        {{ translate.btn_delete }}
      </button>
      <button v-if="isReplace()" @click="deleteAccount(false)" class="btn btn-danger btn-sm whitespace-nowrap">
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
            stroke-width="2"
            d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.btn_replace }}
      </button>
    </div>
  </div>

  <modal
    :id="'modal-disabled-user'"
    :show="modal.disabled"
    @close-modal="hideDisabledModale"
    :option-show-close-btn="false"
  >
    <template #icon>
      <svg class="h-6 w-6 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
    </template>
    <template #title> {{ translate.disabled_title }} </template>
    <template #body>
      <div v-if="!confirm_disabled">
        <svg
          class="icon float-start me-1"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
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
        {{ translate.disabled_content_1 }} <br />
        <p class="mt-2">
          <i>{{ translate.disabled_content_2 }}</i>
        </p>
      </div>
      <div v-else>
        <p v-html="msg_return"></p>
      </div>
    </template>
    <template #footer>
      <button v-if="!confirm_disabled" type="button" class="btn btn-outline-dark btn-sm" @click="hideModal('disabled')">
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
        {{ translate.disabled_btn_cancel }}
      </button>
      <button type="button" class="btn btn-danger btn-sm" @click="disabled(true)">
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
        {{ translate.disabled_btn_confirm }}
      </button>
    </template>
  </modal>

  <modal :id="'modal-delete-user'" :show="modal.delete" @close-modal="hideDeleteModale" :option-show-close-btn="false">
    <template #icon>
      <svg class="h-6 w-6 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
    </template>
    <template #title>
      <span v-if="isDelete()">{{ translate.delete_title }}</span>
      <span v-if="isReplace()">{{ translate.replace_title }}</span>
    </template>
    <template #body>
      <div v-if="!confirm_delete">
        <div v-if="isDelete()">
          <svg
            class="inline-block w-5 h-5 align-text-bottom"
            aria-hidden="true"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          {{ translate.delete_content_1 }} <br />
          <p class="mt-2">
            <i>{{ translate.delete_content_2 }}</i>
          </p>
        </div>
        <div v-if="isReplace()">
          <svg
            class="inline-block w-5 h-5 align-text-bottom"
            aria-hidden="true"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          {{ translate.replace_content_1 }} <br />
          <p class="mt-2">
            <i>{{ translate.replace_content_2 }}</i>
          </p>
        </div>
      </div>
      <div v-else>
        <p v-html="msg_return"></p>
      </div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal('delete')">
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

        <span v-if="!confirm_delete && isDelete()">
          {{ translate.delete_btn_cancel }}
        </span>
        <span v-if="!confirm_delete && isReplace()">
          {{ translate.replace_btn_cancel }}
        </span>
      </button>

      <button type="button" class="btn btn-danger btn-sm" @click="deleteAccount(true)">
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

        <span v-if="isDelete()"> {{ translate.delete_btn_confirm }}</span>
        <span v-if="isReplace()"> {{ translate.replace_btn_confirm }}</span>
      </button>
    </template>
  </modal>
</template>

<style scoped></style>

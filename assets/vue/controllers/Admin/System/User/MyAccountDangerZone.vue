<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer les events pour la danger zone
 */

import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'MyAccountDangerZone',
  props: {
    url_disabled: String,
    url_delete: String,
    translate: Object,
    can_delete: String,
    can_replace: String,
  },
  data() {
    return {
      modalDisabled: '',
      confirm_disabled: false,
      msg_return: this.translate.loading,
      url_reload: '',
      modalDelete: '',
      confirm_delete: false,
    };
  },
  mounted() {
    this.modalDisabled = new Modal(document.getElementById('modal-alert-disabled'), {});
    this.modalDelete = new Modal(document.getElementById('modal-alert-delete'), {});
  },

  methods: {
    /**
     * Action pour le delete
     */
    delete(confirm) {
      this.confirm_delete = confirm;
      if (!confirm) {
        this.modalDelete.show();
      } else {
        axios
          .post(this.url_delete, {})
          .then((response) => {
            this.msg_return = response.data.msg;
            this.url_reload = response.data.redirect;
          })
          .catch((error) => {
            console.error(error);
          })
          .finally(() => {
            setTimeout(() => {
              document.location.href = this.url_reload;
            }, 3000);
          });
      }
    },

    /**
     * Action pour désactiver le compte
     */
    disabled(confirm) {
      this.confirm_disabled = confirm;
      if (!confirm) {
        this.modalDisabled.show();
      } else {
        axios
          .post(this.url_disabled, {})
          .then((response) => {
            this.msg_return = response.data.msg;
            this.url_reload = response.data.redirect;
          })
          .catch((error) => {
            console.error(error);
          })
          .finally(() => {
            setTimeout(() => {
              document.location.href = this.url_reload;
            }, 3000);
          });
      }
    },

    isDelete() {
      return !!(this.can_delete === '1' && this.can_replace === '0');
    },

    isReplace() {
      return this.can_delete === '1' && this.can_replace === '1';
    },
  },
};
</script>

<template>
  <div class="space-y-4">
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-lg"
      style="background-color: var(--alert-danger-bg); border: 1px solid var(--alert-danger-border)"
    >
      <div>
        <h3 class="font-semibold mb-1" style="color: var(--alert-danger-text)">{{ this.translate.disabled_title }}</h3>
        <p class="text-sm" style="color: var(--alert-danger-text); opacity: 0.8">
          {{ this.translate.disabled_description }}
        </p>
      </div>
      <button class="btn btn-outline-dark btn-md whitespace-nowrap">{{ this.translate.btn_disabled }}</button>
    </div>

    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-lg"
      style="background-color: var(--alert-danger-bg); border: 1px solid var(--alert-danger-border)"
    >
      <div>
        <h3 class="font-semibold mb-1" style="color: var(--alert-danger-text)">
          <span v-if="this.isDelete()">{{ this.translate.delete_title }}</span>
          <span v-if="this.isReplace()">{{ this.translate.replace_title }}</span>
        </h3>
        <p class="text-sm" style="color: var(--alert-danger-text); opacity: 0.8">
          <span v-if="this.isDelete()">{{ this.translate.delete_description }}</span>
          <span v-if="this.isReplace()">{{ this.translate.replace_description }}</span>
        </p>
      </div>
      <button v-if="this.isDelete()" class="btn btn-danger btn-md whitespace-nowrap">
        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          ></path>
        </svg>
        {{ this.translate.btn_delete }}
      </button>
      <button v-if="this.isReplace()" class="btn btn-danger btn-md whitespace-nowrap">
        <svg
          class="icon-sm"
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

        {{ this.translate.btn_replace }}
      </button>
    </div>
  </div>

  <!-- Modal disabled -->
  <div
    class="modal fade"
    id="modal-alert-disabled"
    tabindex="-1"
    aria-labelledby="modal-alert"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger">
          <h1 class="modal-title fs-5 text-white">{{ this.translate.disabled_title }}</h1>
          <button
            v-if="!this.confirm_disabled"
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div v-if="!this.confirm_disabled">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ this.translate.disabled_content_1 }} <br />
            <p class="mt-2">
              <i>{{ this.translate.disabled_content_2 }}</i>
            </p>
          </div>
          <div v-else>
            <p v-html="this.msg_return"></p>
          </div>
        </div>
        <div class="modal-footer">
          <button v-if="!this.confirm_disabled" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            {{ this.translate.disabled_btn_cancel }}
          </button>
          <button type="button" class="btn btn-danger" @click="this.disabled(true)">
            {{ this.translate.disabled_btn_confirm }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal delete -->
  <div
    class="modal fade"
    id="modal-alert-delete"
    tabindex="-1"
    aria-labelledby="modal-alert"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger">
          <h1 v-if="this.isDelete()" class="modal-title fs-5 text-white">{{ this.translate.delete_title }}</h1>
          <h1 v-if="this.isReplace()" class="modal-title fs-5 text-white">{{ this.translate.replace_title }}</h1>
          <button
            v-if="!this.confirm_delete"
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div v-if="!this.confirm_delete">
            <div v-if="this.isDelete()">
              <i class="bi bi-exclamation-triangle-fill"></i> {{ this.translate.delete_content_1 }} <br />
              <p class="mt-2">
                <i>{{ this.translate.delete_content_2 }}</i>
              </p>
            </div>
            <div v-if="this.isReplace()">
              <i class="bi bi-exclamation-triangle-fill"></i> {{ this.translate.replace_content_1 }} <br />
              <p class="mt-2">
                <i>{{ this.translate.replace_content_2 }}</i>
              </p>
            </div>
          </div>
          <div v-else>
            <p v-html="this.msg_return"></p>
          </div>
        </div>
        <div class="modal-footer">
          <button
            v-if="!this.confirm_delete && this.isDelete()"
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            {{ this.translate.delete_btn_cancel }}
          </button>
          <button
            v-if="!this.confirm_delete && this.isReplace()"
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            {{ this.translate.replace_btn_cancel }}
          </button>
          <button v-if="this.isDelete()" type="button" class="btn btn-danger" @click="this.delete(true)">
            {{ this.translate.delete_btn_confirm }}
          </button>
          <button v-if="this.isReplace()" type="button" class="btn btn-danger" @click="this.delete(true)">
            {{ this.translate.replace_btn_confirm }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <button class="btn btn-danger me-3" @click="this.disabled(false)">{{ this.translate.btn_disabled }}</button>
  <button v-if="this.isDelete()" class="btn btn-danger" @click="this.delete(false)">
    {{ this.translate.btn_delete }}
  </button>
  <button v-if="this.isReplace()" class="btn btn-danger" @click="this.delete(false)">
    {{ this.translate.btn_replace }}
  </button>
</template>

<style scoped></style>

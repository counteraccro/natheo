<script>

import axios from "axios";
import Toast from "../../../Components/Global/Toast.vue";
import {copyToClipboard} from "../../../../utils/copyToClipboard";
import Modal from "../../../Components/Global/Modal.vue";

export default {
  name: "ApiToken",
  components: {
    Modal,
    Toast
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
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      }
    }
  },
  mounted() {
    //this.loadData();
  },
  methods: {

    /**
     * Génère un token
     */
    generateToken() {
      this.loading = true;
      axios.get(this.urls.generate_token).then((response) => {
        this.apiToken.token = response.data.token;
        this.toasts.toastSuccess.show = true;
        this.toasts.toastSuccess.msg = this.translate.generate_token_success;
      }).catch((error) => {
        console.error(error);
      }).finally(() => this.loading = false);
    },

    /**
     * Permet de copier coller le token
     */
    copyToken()
    {
      copyToClipboard(this.apiToken.token).then(() => {
        this.toasts.toastSuccess.show = true;
        this.toasts.toastSuccess.msg = this.translate.token_copy_success;
      })
    },

    saveToken(isConfirm)
    {
      if(!isConfirm) {
        this.showModalApiTokenConfirm = true;
        return false;
      }

      this.loading = true;
      axios.post(this.urls.save_api_token, {
        apiToken: this.apiToken
      }).then((response) => {
        //this.toasts.toastSuccess.show = true;
        //this.toasts.toastSuccess.msg = this.translate.generate_token_success;
      }).catch((error) => {
        console.error(error);
      }).finally(() => this.loading = false);
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },

    /**
     * Ferme la modale
     */
    hideModal() {
      this.showModalApiTokenConfirm = false;
    },
  }
}
</script>

<template>

  <div :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loading }}</span>
      </div>
    </div>

    <div class="card  border border-secondary">
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
          <input type="text" class="form-control" :placeholder="this.translate.title_placeholder" id="api-token-name" v-model="this.apiToken.name">
          <div class="form-text">{{ this.translate.title_help }}</div>
        </div>

        <div class="mb-3">
          <label for="api-token-name" class="form-label">{{ this.translate.comment_label }}</label>
          <textarea type="text" class="form-control" :placeholder="this.translate.comment_placeholder" id="api-token-name" v-model="this.apiToken.comment"></textarea>
          <div class="form-text">{{ this.translate.comment_help }}</div>
        </div>

        <div class="mb-3">
          <div v-if="this.apiToken.id === null">
            <div class="input-group mb-3">
              <input type="text" class="form-control" id="token" v-model="this.apiToken.token" disabled/>
              <button class="btn btn-secondary" type="button" @click="this.generateToken()">
                <i class="bi bi-gear"></i> {{ this.translate.btn_new_token }}
              </button>
            </div>
          </div>

          <div v-else>
            <div class="input-group">
              <input type="text" class="form-control" id="token" v-model="this.apiToken.token" disabled/>
              <button class="btn btn-secondary" type="button" @click="this.generateToken()">
                <i class="bi bi-gear"></i> {{ this.translate.btn_new_token }}
              </button>
              <button class="btn btn-secondary" type="button" @click="this.copyToken()">
                <i class="bi bi-copy"></i> {{ this.translate.btn_copy_past }}
              </button>
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
          <div v-if="this.apiToken.id === null" class="btn btn-primary">New</div>
          <div v-else class="btn btn-secondary" @click="this.saveToken(false)"><i class="bi bi-pencil-square"></i> {{ this.translate.btn_edit_token_api }}</div>
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
    <template #title>
      <i class="bi bi-sign-stop"></i> {{ this.translate.modale_title_confirm_edit }}
    </template>
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
        @close-toast="this.closeToast('toastError')"
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
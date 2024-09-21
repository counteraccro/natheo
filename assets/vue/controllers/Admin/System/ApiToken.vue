<script>

import axios from "axios";
import Toast from "../../../Components/Global/Toast.vue";

export default {
  name: "ApiToken",
  components: {
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
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
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
          <div v-if="this.apiToken.id === null">
            <div class="input-group mb-3">
              <input type="text" class="form-control" id="token" v-model="this.apiToken.token" disabled/>
              <button class="btn btn-outline-secondary" type="button" @click="this.generateToken()">
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
              <button class="btn btn-secondary" type="button" id="inputGroupFileAddon03">
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

          </div>
        </div>

      </div>

    </div>

  </div>

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
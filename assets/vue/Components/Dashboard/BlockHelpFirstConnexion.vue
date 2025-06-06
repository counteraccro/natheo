<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card help première connexion
 */
import axios from "axios";
import Modal from "../Global/Modal.vue";

;

export default {
  name: "BlockHelpFirstConnexion",
  components: {Modal},
  emit: [],
  props: {
    urls: Object,
    translate: Object,
    datas: Object,
  },
  emits: ['reload-grid', 'hide-block'],
  data() {
    return {
      hide: false,
      loading: false,
      result: [],
      errorMessage: null,
      hideMsgSuccess: null,
      complete: false,
      showModalConfirm: false,
    }
  },
  mounted() {
    this.load();
  },
  methods: {

    /**
     * Charge les données du block
     */
    load() {
      this.loading = true;
      axios.get(this.urls.load_block_dashboard).then((response) => {
        if (response.data.success === false) {
          this.errorMessage = response.data.error;
        } else {
          this.result = response.data.body;
          this.complete = response.data.configComplete;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
        this.reload();
      });
    },

    /**
     * Rechargement du grid
     */
    reload() {
      this.$emit("reload-grid");
    },

    /**
     * Masque le bloc de façon définitive
     */
    hideConfig() {
      this.loading = true;
      this.hideModal();

      axios.post(this.urls.update_user_data, {
        key: this.datas.user_data_key_first_connexion,
        value: 0
      }).then((response) => {
        if (response.data.success) {
          this.hideMsgSuccess = true;
        } else {
          console.error(response.data.msg);
        }
      }).catch(function (error) {
        console.error(error);
      }).finally(() => {
        this.loading = false;
        setTimeout(this.hideBlock, 3000);

      });
    },

    /**
     * Masque le block
     */
    hideBlock() {
      this.$emit("hide-block");
      this.reload();
    },

    /**
     * Affichage la modal
     */
    showModal() {
      this.showModalConfirm = true;
    },

    /**
     * Ferme la modal
     */
    hideModal() {
      this.showModalConfirm = false;
    },
  }
}
</script>

<template>
  <div class="card">
    <h5 class="card-header"><i class="bi bi-info-circle"></i> {{ this.translate.title }} </h5>

    <div class="card-body" v-if="this.loading">
      <div class="spinner-border spinner-border-sm text-secondary" role="status">
        <span class="visually-hidden">{{ this.translate.loading }}</span>
      </div>
      {{ this.translate.loading }}
    </div>

    <div class="card-body" v-else>

      <div v-if="this.errorMessage !== null">
        <i class="bi bi-exclamation-circle"></i> {{ this.errorMessage }}
      </div>
      <div v-else-if="this.hideMsgSuccess !== null">
        <i class="text-success"> <i class="bi bi-check-circle"></i> {{ this.translate.msg_hide_success }}</i>
      </div>
      <div v-else>

        <h5 class="card-title"> {{ this.translate.sub_title }}</h5>
        <p class="card-text">{{ this.translate.text_1 }}</p>

        <ul style="list-style: none;">
          <li v-for="(item, index) in this.result" :key="index">
            <span v-if="item.success" class="text-success">
              <i class="bi bi-check-circle"></i> {{ item.msg }}
            </span>
            <span v-else-if="!Array.isArray(item.msg)" class="text-warning">
              <i class="bi bi-exclamation-circle"></i> {{ item.msg }}
            </span>
            <span v-else class="text-warning">
              <i class="bi bi-exclamation-circle"></i> {{ item.msgTitle }} :
              <ul style="list-style: none;">
                <li v-for="(subItem, index) in item.msg" :key="index"> <i class="bi bi-arrow-return-right"></i> {{ subItem }}</li>
              </ul>
            </span>
          </li>
        </ul>

          <p v-if="this.complete" class="card-text">{{ this.translate.text_end_success }}</p>
          <p v-else class="card-text">{{ this.translate.text_end }}</p>

          <div class="float-end">
            <div class="btn btn-secondary btn-sm me-2" @click="this.load()"><i class="bi bi-arrow-clockwise"></i></div>
            <div v-if="!this.complete" class="btn btn-secondary btn-sm" @click="this.showModal()">{{ this.translate.btn_def_hide }}</div>
            <div v-else class="btn btn-secondary btn-sm" @click="this.hideConfig()">{{ this.translate.btn_def_hide }}</div>
          </div>
      </div>
    </div>

  </div>

  <modal
      :id="'modal-config-hide-help-config'"
      :show="this.showModalConfirm"
      @close-modal="this.hideModal"
      :option-show-close-btn="false"
  >
    <template #title>
      <i class="bi bi-sign-stop"></i> {{ translate.modal_confirm_title }}
    </template>
    <template #body>
      <div> {{ translate.modal_confirm_body_1 }}</div>
      <div> {{ translate.modal_confirm_body_2 }}</div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary" @click="this.hideConfig()">
        <i class="bi bi-check2-circle"></i> {{ translate.modal_confirm_btn_ok }}
      </button>
      <button type="button" class="btn btn-secondary" @click="this.hideModal()">
        <i class="bi bi-x-circle"></i> {{ translate.modal_confirm_btn_ko }}
      </button>
    </template>
  </modal>
</template>
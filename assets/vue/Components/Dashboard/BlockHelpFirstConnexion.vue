<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card help première connexion
 */
import axios from 'axios';
import Modal from '../Global/Modal.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import AlertSuccess from '@/vue/Components/Alert/Success.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';

export default {
  name: 'BlockHelpFirstConnexion',
  components: { AlertDanger, AlertSuccess, SkeletonText, Modal },
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
    };
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
      axios
        .get(this.urls.load_block_dashboard)
        .then((response) => {
          if (response.data.success === false) {
            this.errorMessage = response.data.error;
          } else {
            this.result = response.data.body;
            this.complete = response.data.configComplete;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.reload();
        });
    },

    /**
     * Rechargement du grid
     */
    reload() {
      this.$emit('reload-grid');
    },

    /**
     * Masque le bloc de façon définitive
     */
    hideConfig() {
      this.loading = true;
      this.hideModal();

      axios
        .post(this.urls.update_user_data, {
          key: this.datas.user_data_key_first_connexion,
          value: 0,
        })
        .then((response) => {
          if (response.data.success) {
            this.hideMsgSuccess = true;
          } else {
            console.error(response.data.msg);
          }
        })
        .catch(function (error) {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          setTimeout(this.hideBlock, 3000);
        });
    },

    /**
     * Masque le block
     */
    hideBlock() {
      this.$emit('hide-block');
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
  },
};
</script>

<template>
  <div class="card rounded-lg overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b flex items-center justify-between" style="border-color: var(--border-color)">
      <h3 class="text-lg font-semibold">{{ this.translate.title }}</h3>

      <div class="flex gap-2">
        <a href="#" @click="this.load()" class="text-sm font-medium hover:underline text-[var(--primary)]">
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
        </a>
        <a
          href="#"
          class="text-sm font-medium hover:underline text-[var(--primary)]"
          @click="!this.complete ? this.showModal() : this.hideConfig()"
        >
          {{ this.translate.btn_def_hide }}
        </a>
      </div>
    </div>

    <div class="p-4" v-if="!this.loading">
      <AlertDanger v-if="this.errorMessage !== null" :text="this.errorMessage" />

      <AlertSuccess v-if="this.hideMsgSuccess" :text="this.translate.msg_hide_success" />
      <div v-else>Content</div>
    </div>
    <div class="p-4" v-else>
      <SkeletonText />
    </div>
  </div>

  <!--<div class="card-body" v-if="this.loading">
      <div class="spinner-border spinner-border-sm text-secondary" role="status">
        <span class="visually-hidden">{{ this.translate.loading }}</span>
      </div>
      {{ this.translate.loading }}
    </div>

    <div class="card-body" v-else>
      <div v-if="this.errorMessage !== null"><i class="bi bi-exclamation-circle"></i> {{ this.errorMessage }}</div>
      <div v-else-if="this.hideMsgSuccess !== null">
        <i class="text-success"> <i class="bi bi-check-circle"></i> {{ this.translate.msg_hide_success }}</i>
      </div>
      <div v-else>
        <h5 class="card-title">{{ this.translate.sub_title }}</h5>
        <p class="card-text">{{ this.translate.text_1 }}</p>

        <ul style="list-style: none">
          <li v-for="(item, index) in this.result" :key="index">
            <span v-if="item.success" class="text-success"> <i class="bi bi-check-circle"></i> {{ item.msg }} </span>
            <span v-else-if="!Array.isArray(item.msg)" class="text-warning">
              <i class="bi bi-exclamation-circle"></i> {{ item.msg }}
            </span>
            <span v-else class="text-warning">
              <i class="bi bi-exclamation-circle"></i> {{ item.msgTitle }} :
              <ul style="list-style: none">
                <li v-for="(subItem, index) in item.msg" :key="index">
                  <i class="bi bi-arrow-return-right"></i> {{ subItem }}
                </li>
              </ul>
            </span>
          </li>
        </ul>

        <p v-if="this.complete" class="card-text">{{ this.translate.text_end_success }}</p>
        <p v-else class="card-text">{{ this.translate.text_end }}</p>

        <div class="float-end">
          <div class="btn btn-secondary btn-sm me-2" @click="this.load()"><i class="bi bi-arrow-clockwise"></i></div>
          <div v-if="!this.complete" class="btn btn-secondary btn-sm" @click="this.showModal()">
            {{ this.translate.btn_def_hide }}
          </div>
          <div v-else class="btn btn-secondary btn-sm" @click="this.hideConfig()">
            {{ this.translate.btn_def_hide }}
          </div>
        </div>
      </div>
    </div>
  </div>-->

  <modal
    :id="'modal-config-hide-help-config'"
    :show="this.showModalConfirm"
    @close-modal="this.hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.modal_confirm_title }} </template>
    <template #body>
      <div>{{ translate.modal_confirm_body_1 }}</div>
      <div>{{ translate.modal_confirm_body_2 }}</div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="this.hideConfig()">
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
        {{ translate.modal_confirm_btn_ok }}
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
        {{ translate.modal_confirm_btn_ko }}
      </button>
    </template>
  </modal>
</template>

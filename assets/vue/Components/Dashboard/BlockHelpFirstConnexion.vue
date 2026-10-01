<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card help première connexion
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import Modal from '../Global/Modal.vue';
import SkeletonText from '@/vue/Components/Skeleton/Text.vue';
import AlertSuccess from '@/vue/Components/Alert/Success.vue';
import AlertDanger from '@/vue/Components/Alert/Danger.vue';
import type {
  BlockHelpFirstConnexionUrls,
  BlockHelpFirstConnexionTranslate,
  BlockHelpFirstConnexionDatas,
  ConfigCheckItem,
  ConfigLinks,
  LoadBlockDashboardResponse,
  UpdateUserDataResponse,
} from '@/ts/Dashboard/BlockHelpFirstConnexion.type';

export default defineComponent({
  name: 'BlockHelpFirstConnexion',
  components: { AlertDanger, AlertSuccess, SkeletonText, Modal },
  props: {
    urls: {
      type: Object as PropType<BlockHelpFirstConnexionUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<BlockHelpFirstConnexionTranslate>,
      required: true,
    },
    datas: {
      type: Object as PropType<BlockHelpFirstConnexionDatas>,
      required: true,
    },
  },
  emits: ['reload-grid', 'hide-block'],
  data() {
    return {
      hide: false as boolean,
      loading: false as boolean,
      result: [] as ConfigCheckItem[],
      errorMessage: null as string | null,
      hideMsgSuccess: null as boolean | null,
      complete: false as boolean,
      links: {} as ConfigLinks,
      showModalConfirm: false as boolean,
    };
  },
  mounted() {
    this.load();
  },
  methods: {
    /**
     * Charge les données du block
     */
    load(): void {
      this.loading = true;
      axios
        .get<LoadBlockDashboardResponse>(this.urls.load_block_dashboard)
        .then((response) => {
          if (response.data.success === false) {
            this.errorMessage = response.data.error;
          } else {
            this.result = response.data.body;
            this.complete = response.data.configComplete;
            this.links = response.data.links;
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
    reload(): void {
      this.$emit('reload-grid');
    },

    /**
     * Masque le bloc de façon définitive
     */
    hideConfig(): void {
      this.loading = true;
      this.hideModal();

      axios
        .post<UpdateUserDataResponse>(this.urls.update_user_data, {
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
        .catch((error) => {
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
    hideBlock(): void {
      this.$emit('hide-block');
      this.reload();
    },

    /**
     * Affichage la modal
     */
    showModal(): void {
      this.showModalConfirm = true;
    },

    /**
     * Ferme la modal
     */
    hideModal(): void {
      this.showModalConfirm = false;
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg
            class="card-icon text-[color:var(--primary)]"
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
              d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"
            />
          </svg>
          {{ translate.title }}
        </div>
      </div>
      <div class="flex gap-2">
        <a href="#" @click="load()" class="text-sm font-medium hover:underline text-[var(--primary)]">
          <svg
            class="card-icon"
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
          @click="!complete ? showModal() : hideConfig()"
        >
          {{ translate.btn_def_hide }}
        </a>
      </div>
    </div>
    <div class="p-5">
      <div v-if="!loading">
        <AlertDanger v-if="errorMessage !== null" :text="errorMessage" />

        <AlertSuccess v-if="hideMsgSuccess" :text="translate.msg_hide_success" />
        <div v-else>
          <h4 class="font-semibold mb-2 text-[var(--text-primary)]">{{ translate.sub_title }}</h4>
          <p class="text-sm mb-4 text-[var(--text-secondary)]">{{ translate.text_1 }}</p>

          <div class="space-y-1">
            <div v-for="(item, index) in result" :key="index">
              <div v-if="item.success" class="config-item config-item-success">
                <svg class="config-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                <span> {{ item.msg }} </span>
              </div>

              <div v-else-if="!Array.isArray(item.msg)" class="config-item config-item-error">
                <svg class="config-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                <span> {{ item.msg }} </span>
              </div>
              <div v-else>
                <div class="config-item config-item-error">
                  <svg class="config-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                  </svg>
                  <span> {{ item.msgTitle }} </span>
                </div>

                <div
                  v-for="(subItem, subIndex) in item.msg"
                  :key="subIndex"
                  class="config-item config-item-error config-item-nested"
                >
                  <svg class="config-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                  </svg>
                  <span>{{ subItem }} </span>
                </div>

                <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-[var(--border-color)]">
                  <a
                    :href="links.link_options.link"
                    class="text-sm font-medium hover:underline flex items-center gap-1.5 text-[color:var(--primary)]"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm16 14a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2ZM4 13a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6Zm16-2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6Z"
                      ></path>
                    </svg>

                    {{ links.link_options.label }}
                  </a>
                  <a
                    :href="links.link_tokens.link"
                    class="text-sm font-medium hover:underline flex items-center gap-1.5 text-[color:var(--primary)]"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                      ></path>
                    </svg>
                    {{ links.link_tokens.label }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
        <SkeletonText />
      </div>
    </div>
  </div>

  <modal
    :id="'modal-config-hide-help-config'"
    :show="showModalConfirm"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.modal_confirm_title }} </template>
    <template #body>
      <div>{{ translate.modal_confirm_body_1 }}</div>
      <div>{{ translate.modal_confirm_body_2 }}</div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="hideConfig()">
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
        {{ translate.modal_confirm_btn_ko }}
      </button>
    </template>
  </modal>
</template>

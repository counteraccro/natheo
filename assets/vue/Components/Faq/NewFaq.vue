<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Formulaire de création d'une nouvelle FAQ
 */

import { defineComponent, type PropType } from 'vue';
import SkeletonFaq from '@/vue/Components/Skeleton/Faq.vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import Toast from '@/vue/Components/Global/Toast.vue';
import success from '@/vue/Components/Alert/Success.vue';

type TranslateRecord = { [key: string]: string | TranslateRecord };

type Toast = {
  show: boolean;
  msg: string;
};

type Toasts = {
  [key: string]: Toast;
};

export default defineComponent({
  name: 'NewFaq',
  components: { Toast, SkeletonFaq },
  props: {
    urls: { type: Object as PropType<Record<string, string>>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  data() {
    return {
      loading: false,
      isTouched: false,
      faq: {
        title: null,
      },
      toasts: {
        success: {
          show: false,
          msg: '',
        },
        error: {
          show: false,
          msg: '',
        },
      } as Toasts,
    };
  },
  mounted(): any {},
  computed: {
    /**
     * Vérifie si le champ nouveau titre est vide ou non
     * @return boolean
     */
    checkIsValid(): boolean {
      return this.faq.title !== '' && this.faq.title !== null;
    },

    /**
     * Affiche une erreur ou non
     * @return boolean
     */
    showError(): boolean {
      return this.isTouched && !this.checkIsValid;
    },
  },
  methods: {
    /**
     * Détecte un changement
     */
    onTitleInput(): void {
      this.isTouched = true;
    },

    /**
     * Créer une nouvelle FAQ
     */
    newFaq(): void {
      if (!this.checkIsValid) {
        return;
      }

      this.loading = true;
      axios
        .post(this.urls.new_faq, {
          title: this.faq.title,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.success.msg = response.data.msg;
            this.toasts.success.show = true;
            emitter.emit('reset-check-confirm');
            setTimeout(function () {
              window.location.replace(response.data.url_redirect);
            }, 2000);
          } else {
            this.toasts.error.msg = response.data.msg;
            this.toasts.error.show = true;
            this.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {});
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <div v-if="loading" class="mt-5">
    <SkeletonFaq :is-new="true" />
  </div>
  <div v-else>
    <div class="card rounded-lg p-6 mb-4 mt-4">
      <div class="border-b-1 border-b-[var(--border-color)] mb-4">
        <h2 class="text-lg font-bold text-[var(--text-primary)]">{{ translate.new_faq }}</h2>
        <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">{{ translate.new_faq_sub_title }}</p>
      </div>

      <div class="form-group">
        <label class="form-label">{{ translate.new_faq_input_title }}</label>
        <input
          type="text"
          class="form-input"
          v-model="faq.title"
          @input="onTitleInput"
          :class="{ 'is-invalid': showError }"
        />
        <span v-if="showError" class="form-text text-error">✗ {{ translate.new_faq_error_empty }}</span>
        <span v-else class="form-text">{{ translate.new_faq_help }}</span>
      </div>

      <div class="flex flex-wrap gap-3 pt-4 mt-5 flex-row-reverse">
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
            ></path>
          </svg>
          {{ translate.new_faq_btn_cancel }}
        </button>
        <button class="btn btn-sm btn-primary" :disabled="!checkIsValid" @click="newFaq">
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ translate.new_faq_btn }}
        </button>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :type="'success'" :show="toasts.success.show" @close-toast="closeToast('success')">
      <template #body>
        <div v-html="toasts.success.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.error.show" @close-toast="closeToast('error')">
      <template #body>
        <div v-html="toasts.error.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style scoped></style>

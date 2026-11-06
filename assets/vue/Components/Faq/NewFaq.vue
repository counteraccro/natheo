<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Formulaire de création d'une nouvelle FAQ
 */

import { defineComponent, type PropType } from 'vue';
import SkeletonFaq from '@/vue/Components/Skeleton/Faq.vue';
import axios from 'axios';
import { withCsrf } from '@/vue/Composables/Admin/csrf';
import { emitter } from '@/vue/Composables/Global/useEvent';
import { toastSuccess, toastError } from '@/vue/Composables/Global/toast';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'NewFaq',
  components: { SkeletonFaq },
  props: {
    urls: { type: Object as PropType<Record<string, string>>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    csrf_token: { type: String, required: true },
  },
  data() {
    return {
      loading: false,
      isTouched: false,
      faq: {
        title: null,
      },
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
        .post(
          this.urls.new_faq,
          {
            title: this.faq.title,
          },
          withCsrf(this.csrf_token)
        )
        .then((response) => {
          if (response.data.success === true) {
            toastSuccess(response.data.msg);
            emitter.emit('reset-check-confirm');
            setTimeout(function () {
              window.location.replace(response.data.url_redirect);
            }, 2000);
          } else {
            toastError(response.data.msg);
            this.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
          if (error.response?.data?.msg) {
            toastError(error.response.data.msg);
          }
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <div v-if="loading" class="mt-5">
    <SkeletonFaq :is-new="true" />
  </div>
  <div v-else>
    <div class="card mb-4">
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
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9.529 9.988a2.502 2.502 0 1 1 5 .191A2.441 2.441 0 0 1 12 12.582V14m-.01 3.008H12M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
              />
            </svg>
            {{ translate.new_faq }}
          </div>
          <p class="card-subtitle">
            {{ translate.new_faq_sub_title }}
          </p>
        </div>
      </div>
      <div class="p-5">
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

        <div class="flex flex-wrap justify-end gap-2 pt-4 mt-5">
          <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.history.back()">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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
  </div>
</template>

<style scoped></style>

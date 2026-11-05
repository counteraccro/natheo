<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer les events pour more options
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import type { MyAccountMoreOptionsDatas, MyAccountMoreOptionsTranslate } from '@/ts/User/MyAccountMoreOptions.type';
import { toastSuccess, toastError, toastRequestError } from '@/vue/Composables/Global/toast';
import { withCsrf } from '@/vue/Composables/Admin/csrf';

export default defineComponent({
  name: 'MyAccountMoreOptions',
  props: {
    translate: { type: Object as PropType<MyAccountMoreOptionsTranslate>, required: true },
    datas: { type: Object as PropType<MyAccountMoreOptionsDatas>, required: true },
    url: { type: String, required: true },
    csrf_token: { type: String, required: true },
  },
  data() {
    return {
      loading: false,
      btnHelpBlock: this.datas.help_first_connexion,
    };
  },

  methods: {
    /**
     * Affiche ou masque l'aide de première connexion
     * @param key clé de la donnée utilisateur
     * @param value 1 pour afficher, 0 pour masquer
     */
    update(key: string, value: number): void {
      this.loading = true;
      axios
        .post(
          this.url,
          {
            key: key,
            value: value,
          },
          withCsrf(this.csrf_token)
        )
        .then((response) => {
          if (response.data.success === true) {
            this.btnHelpBlock = value === 1;
          }
          (response.data.success ? toastSuccess : toastError)(response.data.msg);
        })
        .catch((error) => {
          toastRequestError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <div v-if="loading" class="mb-3">
    <div class="flex flex-wrap gap-3">
      <!-- Skeleton Button 1 -->
      <div role="status" class="animate-pulse">
        <div class="h-10 w-32 bg-gray-300 dark:bg-gray-700 rounded-lg"></div>
      </div>

      <!-- Skeleton Button 2 -->
      <div role="status" class="animate-pulse">
        <div class="h-10 w-28 bg-gray-300 dark:bg-gray-700 rounded-lg"></div>
      </div>

      <!-- Skeleton Button 3 -->
      <div role="status" class="animate-pulse">
        <div class="h-10 w-36 bg-gray-300 dark:bg-gray-700 rounded-lg"></div>
      </div>
    </div>
  </div>
  <div v-else>
    <button
      v-if="!btnHelpBlock"
      class="btn btn-sm btn-secondary"
      :disabled="loading"
      @click="update(datas.user_data_key_first_connexion, 1)"
    >
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
          d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
      {{ translate.btn_show_help }}
    </button>
    <button
      v-else
      class="btn btn-sm btn-secondary"
      :disabled="loading"
      @click="update(datas.user_data_key_first_connexion, 0)"
    >
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
          d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
        />
      </svg>
      {{ translate.btn_hide_help }}
    </button>
  </div>
</template>

<style scoped></style>

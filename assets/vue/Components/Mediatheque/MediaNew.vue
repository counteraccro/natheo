<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Edition d'un média ou dossier
 */

import { defineComponent, type PropType } from 'vue';
import { FileData, TranslateRecord } from '@/ts/Mediatheque/type';
import FileUpload from '@/vue/Components/Global/FileUpload.vue';
import axios from 'axios';
import { Toasts } from '@/ts/Toast/type';
import Toast from '@/vue/Components/Global/Toast.vue';
import SkeletonMediathequeUpload from '@/vue/Components/Skeleton/MediathequeUpload.vue';

export default defineComponent({
  name: 'MediaNew',
  components: { SkeletonMediathequeUpload, Toast, FileUpload },
  props: {
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    currentFolder: { type: Number, required: true },
    url: { type: String, required: true },
  },
  emits: ['close'],
  data() {
    return {
      loading: false,
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
  computed: {},
  methods: {
    /**
     * Télécharge un fichier sur le serveur
     * @param file
     */
    saveMedia(file: FileData) {
      this.loading = true;

      axios
        .post(this.url, {
          file: file,
          folder: this.currentFolder,
        })
        .then((response) => {
          this.toasts.success.show = true;
          this.toasts.success.msg = this.translate.loading_msg_success;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          setTimeout(() => {
            this.$emit('reload', this.currentFolder, false, {});
          }, 2000);
        });
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
  <div class="info-drawer-inner">
    <!-- Header du panneau -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="flex gap-1 items-center font-semibold text-sm" style="color: var(--text-primary)">
        <svg
          class="w-4 h-4 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          style="color: var(--primary)"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
          ></path>
        </svg>
        {{ translate.title }}
      </h3>
      <button
        @click="$emit('close')"
        class="p-1.5 rounded-lg transition"
        style="color: var(--text-secondary)"
        onmouseover="this.style.backgroundColor = 'var(--bg-hover)'"
        onmouseout="this.style.backgroundColor = ''"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <skeleton-mediatheque-upload v-if="loading" />
    <file-upload v-else :max-size="20" :translate="translate" @file-uploaded="saveMedia" />
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

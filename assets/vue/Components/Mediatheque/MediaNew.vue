<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Edition d'un média ou dossier
 */

import { defineComponent, type PropType } from 'vue';
import { FileData, TranslateWithGenericError } from '@/ts/Mediatheque/Mediatheque.type';
import FileUpload from '@/vue/Components/Global/FileUpload.vue';
import axios from 'axios';
import SkeletonMediathequeUpload from '@/vue/Components/Skeleton/MediathequeUpload.vue';
import { toastSuccess, toastError } from '@/vue/Composables/Global/toast';

export default defineComponent({
  name: 'MediaNew',
  components: { SkeletonMediathequeUpload, FileUpload },
  props: {
    translate: { type: Object as PropType<TranslateWithGenericError>, required: true },
    currentFolder: { type: Number, required: true },
    url: { type: String, required: true },
  },
  emits: ['close', 'reload'],
  data() {
    return {
      loading: false,
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
        .then(() => {
          toastSuccess(this.translate.loading_msg_success as string);
          setTimeout(() => {
            this.$emit('reload', this.currentFolder, false, {});
          }, 2000);
        })
        .catch((error) => {
          console.error(error);
          this.loading = false;
          toastError(this.translate.generic_error);
        });
    },
  },
});
</script>

<template>
  <div class="info-drawer-inner">
    <!-- Header du panneau -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="flex gap-1 items-center font-semibold text-sm text-fg">
        <svg class="w-4 h-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        class="p-1.5 rounded-lg transition text-fg-muted"
        onmouseover="this.style.backgroundColor = 'var(--bg-hover)'"
        onmouseout="this.style.backgroundColor = ''"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <SkeletonMediathequeUpload v-if="loading" />
    <FileUpload
      v-else
      :max-size="20"
      accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
      :translate="translate"
      @file-uploaded="saveMedia"
    />
  </div>
</template>

<style scoped></style>

<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Edition d'un média ou dossier
 */
import { defineComponent, type PropType } from 'vue';
import { MediaItem } from '@/ts/Mediatheque/type';
import AlertWarning from '@/vue/Components/Alert/Warning.vue';
import { Toasts } from '@/ts/Toast/type';
import Toast from '@/vue/Components/Global/Toast.vue';
import axios from 'axios';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'MediaEdit',
  components: { Toast, AlertWarning },
  props: {
    data: { type: Object as PropType<MediaItem>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    url: { type: String, required: true },
  },
  emits: ['close', 'reload'],
  data() {
    return {
      save: false,
      isError: false,
      media: {
        id: 0,
        name: '',
        description: '',
      },
      folder: {
        name: '',
        currentFolder: 0,
        editFolder: 0,
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
  mounted(): any {
    if (this.data.type === 'media') {
      this.media.id = this.data.id;
      this.media.name = this.data.title;
      this.media.description = this.data.description;
    }

    if (this.data.type === 'folder') {
      this.folder.name = this.data.name;
      this.folder.currentFolder = this.data.parent;
      this.folder.editFolder = this.data.id;
    }
  },
  computed: {
    /**
     * Vérification des données
     */
    checkData(): void {
      this.isError = false;

      if (this.data.type === 'media') {
        this.media.name = this.media.name.replace(/[^a-zA-Z0-9-]/g, '');
        if (this.media.name === '') {
          this.isError = true;
        }
        if (this.media.description === '') {
          this.isError = true;
        }
      }

      if (this.data.type === 'folder') {
        this.folder.name = this.folder.name.replace(/[^a-zA-Z0-9-]/g, '');
        if (this.folder.name === '') {
          this.isError = true;
        }
      }
    },
  },
  methods: {
    saveMedia(): void {
      axios
        .post(this.url, {
          media: this.media,
        })
        .then((response) => {
          this.toasts.success.show = true;
          this.toasts.success.msg = this.translate.save_media_msg_ok as string;
          this.save = true;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          setTimeout(() => {
            this.$emit('reload', this.data.folder_id, true, this.media);
          }, 2000);
        });
    },

    saveFolder(): void {
      axios
        .post(this.url, this.folder)
        .then((response) => {
          if (response.data.result === 'error') {
            this.toasts.error.show = true;
            this.toasts.error.msg = response.data.msg as string;
          } else {
            this.toasts.success.show = true;
            this.toasts.success.msg = response.data.msg as string;
            this.save = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          setTimeout(() => {
            this.$emit('reload', this.data.parent, true, this.folder);
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
          style="color: var(--text-secondary)"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
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

    <!-- Aperçu miniature -->
    <div class="relative rounded-xl overflow-hidden mb-4" style="border: 1px solid var(--border-color)">
      <img :src="data.thumbnail" alt="" class="w-full object-cover" style="max-height: 130px" />
      <span class="type-badge" :class="data.type === 'folder' ? 'folder' : ''">
        {{ data.type === 'folder' ? translate.folder_tag : data.extension }}
      </span>
    </div>

    <div v-if="data.type === 'media'">
      <div class="form-group mt-4">
        <label class="form-label">{{ translate.input_label_file }}</label>
        <input
          class="form-input"
          :class="isError ? 'is-invalid' : ''"
          type="text"
          @change="checkData"
          :placeholder="translate.input_placeholder as string"
          v-model="media.name"
        />
      </div>

      <div class="form-group mt-4">
        <label class="form-label">{{ translate.input_description }}</label>
        <textarea class="form-input" v-model="media.description" @change="checkData"></textarea>
      </div>
    </div>
    <div v-if="data.type === 'folder'">
      <div class="form-group mt-4">
        <label class="form-label">{{ translate.input_label_folder }}</label>
        <input
          class="form-input"
          :class="isError ? 'is-invalid' : ''"
          type="text"
          @change="checkData"
          :placeholder="translate.input_placeholder_folder as string"
          v-model="folder.name"
        />
      </div>
    </div>

    <div class="flex flex-col gap-2 mt-3">
      <button
        @click="data.type === 'media' ? saveMedia() : saveFolder()"
        class="btn btn-sm"
        :disabled="save || isError"
        :class="isError ? 'btn-danger' : !save ? 'btn-primary' : 'btn-success'"
        v-html="isError ? '✘ ' + translate.btn_error : !save ? translate.btn_save : '✓ ' + translate.save_ok"
      ></button>
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

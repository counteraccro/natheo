<script lang="ts">
/**
 * Permet de modérer 1 commentaire
 * @author Gourdon Aymeric
 * @version 2.0
 */
import { defineComponent } from 'vue';
import axios, { AxiosResponse } from 'axios';
import MarkdownEditor from '../../../../Components/Global/MarkdownEditor/MarkdownEditor.vue';
import Toast from '../../../../Components/Global/Toast.vue';
import { emitter } from '@/utils/useEvent';
import { Translate, Urls, Datas, Toasts, LoadCommentResponse, Comment, SaveCommentResponse } from '@/ts/Comment/type';
import SkeletonCommentEdit from '@/vue/Components/Skeleton/CommentEdit.vue';

export default defineComponent({
  name: 'CommentEdit',

  components: { SkeletonCommentEdit, Toast, MarkdownEditor },

  props: {
    urls: {
      type: Object as () => Urls,
      required: true,
    },
    translate: {
      type: Object as () => Translate,
      required: true,
    },
    datas: {
      type: Object as () => Datas,
      required: true,
    },
  },

  emits: [],

  data() {
    return {
      loading: false as boolean,
      comment: null as Comment | null,
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

  mounted(): void {
    this.load();
  },

  methods: {
    /**
     * Charge les données du commentaire
     */
    load(): void {
      this.loading = true;
      axios
        .get<LoadCommentResponse>(this.urls.load_comment + '/' + this.datas.id)
        .then((response: AxiosResponse<LoadCommentResponse>) => {
          this.comment = response.data.comment;
        })
        .catch((error: unknown) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Met à jour la valeur du commentaire depuis l'éditeur markdown
     * @param id - Identifiant de l'éditeur
     * @param value - Nouveau contenu
     */
    updateValue(id: string, value: string): void {
      if (this.comment && id !== null) {
        this.comment.comment = value;
      }
    },

    /**
     * Sauvegarde le commentaire via l'API
     */
    save(): void {
      this.loading = true;
      axios
        .put<SaveCommentResponse>(this.urls.save, {
          comment: this.comment,
        })
        .then((response: AxiosResponse<SaveCommentResponse>) => {
          if (response.data.success) {
            this.toasts.success.msg = response.data.msg;
            this.toasts.success.show = true;
            emitter.emit('reset-check-confirm');
          } else {
            this.toasts.error.msg = response.data.msg;
            this.toasts.error.show = true;
          }
        })
        .catch((error: unknown) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.load();
        });
    },

    /**
     * Retour
     */
    goBack(): void {
      window.history.back();
    },

    /**
     * Ferme un toast en fonction de son identifiant
     * @param nameToast - Clé du toast à fermer
     */
    closeToast(nameToast: keyof Toasts): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <skeleton-comment-edit v-if="loading" />
  <div v-if="comment">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 flex flex-col gap-6">
        <div class="card rounded-2xl overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                ></path>
              </svg>
              <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.commentTitle }}</span>
            </div>
            <span
              class="text-xs px-2 py-1 rounded-full font-medium"
              style="background-color: var(--primary-lighter); color: var(--primary)"
              >Markdown</span
            >
          </div>
          <MarkdownEditor
            :me-id="String(comment.id)"
            :me-value="comment.comment"
            :me-translate="translate.markdown"
            :me-key-words="[]"
            :me-rows="16"
            :me-save="true"
            :me-preview="false"
            @editor-value="updateValue"
          />
        </div>

        <div class="flex items-center justify-between px-6 py-4 rounded-2xl card">
          <a :href="urls.index" class="inline-flex items-center gap-2 btn btn-sm btn-outline-dark">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              ></path>
            </svg>
            {{ translate.cancel }}
          </a>
          <button
            @click="save"
            class="inline-flex items-center gap-2 px-6 py-2 btn btn-sm btn-primary"
            style="background-color: var(--primary)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ translate.btnEdit }}
          </button>
        </div>
      </div>
      <div class="flex flex-col gap-6"></div>
    </div>
  </div>

  <!--<div id="block-faq" class="h-50" :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loading }}</span>
      </div>
    </div>

    <div v-if="comment" class="comment">
      <fieldset class="mb-4">
        <legend>{{ translate.titleInfo }}</legend>
        <div class="row">
          <div class="col-6">
            <div>
              <span v-html="comment.statusStr"></span>
            </div>
            <div v-if="comment.status === datas.statusModerate" class="mt-2">
              <b>{{ translate.moderationComment }}</b> : <br />
              <textarea
                class="form-control"
                id="moderation-content"
                rows="3"
                v-model="comment.moderationComment"
              ></textarea>
              <div v-if="comment.userModeration">
                {{ translate.moderationAuthor }} : {{ comment.userModeration.login }}
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="float-end">
              {{ translate.author }} : {{ comment.author }} ({{ comment.email }})<br />
              {{ translate.created }} {{ comment.createdAt }}<br />
              {{ translate.ip }} : {{ comment.ip }}<br />
              {{ translate.userAgent }} : {{ comment.userAgent }}<br />
            </div>
          </div>
        </div>
      </fieldset>

      <div class="row mb-4">
        <div class="col-8"></div>
        <div class="col-4">
          <select class="form-select" v-model="comment.status">
            <option
              v-for="(key, status) in datas.status"
              :key="status"
              :value="status"
              :selected="status === comment.status"
            >
              {{ key }}
            </option>
          </select>
        </div>
      </div>

      <MarkdownEditor
        :me-id="String(comment.id)"
        :me-value="comment.comment"
        :me-translate="translate.markdown"
        :me-key-words="[]"
        :me-rows="16"
        :me-save="true"
        :me-preview="false"
        @editor-value="updateValue"
      />

      <div class="btn btn-secondary float-end" :class="loading ? 'disabled' : ''" @click="save">
        {{ translate.btnEdit }}
      </div>
    </div>
  </div>-->

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast id="success" option-class-header="text-success" :show="toasts.success.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.success.msg"></div>
      </template>
    </toast>

    <toast id="error" option-class-header="text-danger" :show="toasts.error.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.error.msg"></div>
      </template>
    </toast>
  </div>
</template>

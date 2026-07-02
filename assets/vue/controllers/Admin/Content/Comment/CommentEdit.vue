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
  <div v-if="!comment">
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
      <!-- Icône -->
      <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
        style="background-color: var(--primary-lighter)"
      >
        <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
          />
        </svg>
      </div>

      <!-- Titre -->
      <p class="text-lg font-bold mb-2" style="color: var(--text-primary)">
        {{ translate.error_no_comment_title }}
      </p>

      <!-- Description -->
      <p class="text-sm max-w-xs mb-6" style="color: var(--text-secondary)">
        {{ translate.error_no_comment_text }}
      </p>

      <!-- Boutons -->
      <div class="flex items-center gap-3">
        <a :href="urls.index" class="btn btn-sm btn-outline-dark flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ translate.btn_back }}
        </a>
      </div>
    </div>
  </div>
  <div v-else>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 flex flex-col gap-6">
        <div class="card mb-4">
          <div class="card-header">
            <div>
              <div class="card-title">
                <svg
                  class="card-icon"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                  ></path>
                </svg>
                {{ translate.commentTitle }}
              </div>
            </div>
          </div>
          <div class="p-5">
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
        </div>
        <div class="flex items-center justify-between px-6 py-4 rounded-lg card">
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

      <div class="flex flex-col gap-6">
        <div class="card mb-4">
          <div class="card-header">
            <div>
              <div class="card-title">
                <svg
                  class="card-icon"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"
                  ></path>
                </svg>
                {{ translate.status }}
              </div>
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-center gap-2 mb-4">
              <span v-html="comment.statusStr"></span>
              <span class="text-xs" style="color: var(--text-light)">{{ translate.status_actuel }}</span>
            </div>

            <div v-if="comment.status == datas.statusModerate" class="mt-2 form-group">
              <label
                class="form-label"
                style="color: var(--text-secondary); font-size: var(--text-xs); font-weight: normal"
                >{{ translate.moderationComment }}</label
              >
              <textarea
                class="form-input"
                id="moderation-content"
                rows="3"
                v-model="comment.moderationComment"
              ></textarea>
              <div v-if="comment.userModeration" class="float-end text-xs mt-1" style="color: var(--text-secondary)">
                {{ translate.moderationAuthor }} : {{ comment.userModeration.login }}
              </div>
            </div>

            <div class="form-group">
              <label
                class="form-label"
                style="color: var(--text-secondary); font-size: var(--text-xs); font-weight: normal"
                >{{ translate.status_label }}</label
              >
              <select class="form-input form-input-sm" v-model="comment.status">
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
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <div>
              <div class="card-title">
                <svg
                  class="card-icon"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"
                  ></path>
                </svg>
                {{ translate.titleInfo }}
              </div>
            </div>
          </div>
          <ul class="divide-y divide-[var(--border-color)]">
            <!-- Auteur -->
            <li class="flex items-center gap-3 px-5 py-4">
              <div
                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                style="background-color: var(--primary-lighter)"
              >
                <svg
                  class="w-4 h-4"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  ></path>
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-medium mb-0.5" style="color: var(--text-secondary)">{{ translate.author }}</p>
                <p class="text-xs font-semibold truncate" style="color: var(--text-primary)">{{ comment.author }}</p>
                <p class="text-xs truncate" style="color: var(--text-light)">{{ comment.email }}</p>
              </div>
            </li>
            <!-- Date -->
            <li class="flex items-center gap-3 px-5 py-4">
              <div
                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                style="background-color: var(--primary-lighter)"
              >
                <svg
                  class="w-4 h-4"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  ></path>
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium mb-0.5" style="color: var(--text-secondary)">{{ translate.created }}</p>
                <p class="text-xs font-semibold" style="color: var(--text-primary)">{{ comment.createdAt }}</p>
              </div>
            </li>
            <!-- IP -->
            <li class="flex items-center gap-3 px-5 py-4">
              <div
                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                style="background-color: var(--primary-lighter)"
              >
                <svg
                  class="w-4 h-4"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                  ></path>
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium mb-0.5" style="color: var(--text-secondary)">{{ translate.ip }}</p>
                <p class="text-xs font-semibold font-mono" style="color: var(--text-primary)">{{ comment.ip }}</p>
              </div>
            </li>
            <!-- Autres infos -->
            <li class="flex items-center gap-3 px-5 py-4">
              <div
                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                style="background-color: var(--primary-lighter)"
              >
                <svg
                  class="w-4 h-4"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"
                  ></path>
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium mb-0.5" style="color: var(--text-secondary)">{{ translate.userAgent }}</p>
                <p class="text-xs font-semibold capitalize" style="color: var(--text-primary)">
                  {{ comment.userAgent }}
                </p>
              </div>
            </li>
          </ul>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <div>
              <div class="card-title">
                <svg
                  class="card-icon"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  ></path>
                </svg>
                {{ translate.page_associated }}
              </div>
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-start gap-3">
              <div
                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center mt-0.5"
                style="background-color: var(--primary-lighter)"
              >
                <svg
                  class="w-4 h-4"
                  style="color: var(--primary)"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  ></path>
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-semibold leading-snug" style="color: var(--text-primary)">
                  {{ comment.page.title }}
                </p>
                <p class="text-xs mt-1" style="color: var(--text-light)">
                  {{ translate.page_created }} {{ comment.createdAt }}
                </p>
                <a
                  :href="comment.page.url"
                  class="inline-flex items-center gap-1 text-xs font-medium mt-2 hover:underline"
                  style="color: var(--primary)"
                >
                  {{ translate.page_link }}
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                    ></path>
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

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

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
        <div class="card rounded-lg overflow-hidden">
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
        <div class="card rounded-lg overflow-hidden">
          <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
            <h3 class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.status }}</h3>
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
        <div class="card rounded-lg overflow-hidden">
          <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
            <h3 class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.titleInfo }}</h3>
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

        <div class="card rounded-lg overflow-hidden">
          <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
            <h3 class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.page_associated }}</h3>
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

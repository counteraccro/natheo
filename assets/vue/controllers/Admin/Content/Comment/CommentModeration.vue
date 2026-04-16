<script lang="ts">
/**
 * Permet de modérer Une liste de commentaire
 * @author Gourdon Aymeric
 * @version 2.0
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import Toast from '../../../../Components/Global/Toast.vue';
import SearchPaginate from '../../../../Components/Global/Search/SearchPaginate.vue';
import { marked } from 'marked';
import { emitter } from '@/utils/useEvent';
import type {
  CommentModerationUrls,
  CommentModerationTranslate,
  CommentModerationDatas,
  CommentListResult,
  ModerationState,
  FiltersState,
  ToastsState,
  UpdateModerationResponse,
} from '@/ts/Comment/commentModeration.type';
import SkeletonCommentModeration from '@/vue/Components/Skeleton/CommentModeration.vue';

export default defineComponent({
  name: 'CommentModeration',

  components: { SkeletonCommentModeration, SearchPaginate, Toast },

  props: {
    urls: {
      type: Object as PropType<CommentModerationUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<CommentModerationTranslate>,
      required: true,
    },
    datas: {
      type: Object as PropType<CommentModerationDatas>,
      required: true,
    },
  },

  emits: [],

  data() {
    return {
      loading: false as boolean,
      result: null as CommentListResult | null,
      allSelected: false,
      moderation: {
        selected: [],
        status: this.datas.defaultStatus,
        moderateComment: '',
      } as ModerationState,
      filters: {
        status: this.datas.defaultStatus,
        pages: '0',
      } as FiltersState,
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
      } as ToastsState,
    };
  },

  mounted() {
    this.load();
  },

  methods: {
    /**
     * Charge la liste des commentaires
     */
    load(): void {
      this.loading = true;
      axios
        .get<CommentListResult>(
          this.urls.filter +
            '/' +
            this.filters.status +
            '/' +
            this.filters.pages +
            '/' +
            this.datas.page +
            '/' +
            this.datas.limit
        )
        .then((response) => {
          this.result = response.data;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Met à jour les commentaires sélectionnés
     */
    moderateComment(): void {
      this.loading = true;
      axios
        .post<UpdateModerationResponse>(this.urls.update, this.moderation)
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
            emitter.emit('reset-check-confirm');
            this.allSelected = false;
            this.resetSelected();
            this.load();
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
            this.loading = false;
          }
        })
        .catch((error) => {
          console.error(error);
        });
    },

    /**
     * Changement de page
     * @param page
     * @param limit
     */
    changePageEvent(page: number, limit: number): void {
      this.datas.page = page;
      this.datas.limit = limit;
      this.load();
    },

    /**
     * Met à jour la liste des commentaires selectionnés
     * @param id
     */
    updateSelected(id: number): void {
      if (this.moderation.selected.find((element) => element === id)) {
        this.moderation.selected = this.moderation.selected.filter((item) => item !== id);
      } else {
        this.moderation.selected.push(id);
      }
    },

    /**
     * Reset les commentaires sélectionnés
     */
    resetSelected(): void {
      this.allSelected = false;
      this.moderation.selected = [];
    },

    /**
     * Sélectionne tous les commentaires affichés
     */
    selectAll(): void {
      if (this.result?.data.length === 0) {
        return;
      }

      if (this.allSelected) {
        this.result?.data.forEach((comment) => {
          this.moderation.selected.push(comment.id);
        });
      } else {
        this.resetSelected();
      }
    },

    /**
     * Vérifie si la checkbox doit être check ou non
     * @param id
     * @return {boolean}
     */
    isChecked(id: number): boolean {
      return !!this.moderation.selected.find((element) => element === id);
    },

    /**
     * Converti du markdown en html
     * @param value
     * @return {string}
     */
    renderHtml(value: string): string {
      return marked(value) as string;
    },

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast: keyof ToastsState): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 flex flex-col gap-4">
      <div class="card flex items-center justify-between px-5 py-3 rounded-lg">
        <div class="form-check" style="margin-bottom: 0">
          <input type="checkbox" class="form-check-input" id="check-all" @change="selectAll" v-model="allSelected" />
          <label class="form-check-label font-medium" for="check-all">{{ translate.comment_select_all }}</label>
        </div>
        <div class="flex items-center gap-3">
          <span
            id="selectionCount"
            class="text-sm font-semibold px-2.5 py-1 rounded-full"
            style="background-color: var(--primary-lighter); color: var(--primary)"
          >
            {{ moderation.selected.length }} {{ translate.selection_comment }}</span
          >
          <button
            @click="resetSelected"
            class="text-sm font-medium hover:underline transition-colors cursor-pointer"
            style="color: var(--text-secondary)"
          >
            {{ translate.comment_unselect_all }}
          </button>
        </div>
      </div>
      <skeleton-comment-moderation v-if="loading" />
      <div v-else-if="result !== null">
        <div
          v-for="comment in result.data"
          :key="comment.id"
          class="card rounded-lg overflow-hidden transition-all mb-4"
          :data-id="comment.id"
        >
          <!-- Header -->
          <div class="flex items-center gap-3 px-5 py-3.5" style="border-bottom: 1px solid var(--border-color)">
            <div class="form-check" style="margin-bottom: 0">
              <input
                type="checkbox"
                class="form-check-input"
                :id="'comment-' + comment.id"
                @change="updateSelected(comment.id)"
                :checked="isChecked(comment.id)"
              />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold truncate" style="color: var(--text-primary)">
                Commentaire <span style="color: var(--primary)">#{{ comment.id }}</span>
                <span class="font-normal" style="color: var(--text-secondary)">
                  — {{ translate.comment_date }} {{ comment.date }} {{ translate.comment_author }}
                </span>
                {{ comment.author }}
                <span class="font-normal" style="color: var(--text-secondary)"> ({{ comment.email }})</span>
              </p>
            </div>
            <div v-html="comment.status"></div>
          </div>
          <!-- Body -->
          <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-(--border-color)">
            <!-- Contenu -->
            <div class="p-5">
              <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-light)">
                {{ translate.comment_comment }}
              </p>
              <p class="text-xs mb-3 flex items-center gap-1.5" style="color: var(--text-secondary)">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  ></path>
                </svg>
                <span class="italic"
                  >{{ translate.comment_page }} <strong>{{ comment.page }}</strong></span
                >
              </p>
              <p
                class="text-sm leading-relaxed"
                style="color: var(--text-primary)"
                v-html="renderHtml(comment.comment)"
              ></p>
            </div>
            <!-- Informations -->

            <div class="p-5">
              <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-light)">
                {{ translate.comment_info }}
              </p>
              <ul class="space-y-2 text-xs mb-4">
                <li class="flex items-center gap-2" style="color: var(--text-secondary)">
                  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                    ></path>
                  </svg>
                  <span class="font-medium text-xs" style="color: var(--text-primary)"
                    >{{ translate.comment_ip }} :</span
                  >
                  <span class="font-mono">{{ comment.ip }}</span>
                </li>
                <li class="flex items-center gap-2" style="color: var(--text-secondary)">
                  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"
                    ></path>
                  </svg>
                  <span class="font-medium text-xs" style="color: var(--text-primary)"
                    >{{ translate.comment_user_agent }} :</span
                  >
                  {{ comment.userAgent }}
                </li>
              </ul>
              <!-- Bloc modération -->
              <div
                v-if="comment.moderator !== null"
                class="rounded-lg p-3 mt-3"
                style="border: 1px solid var(--border-color); background-color: var(--bg-hover)"
              >
                <p class="text-xs font-semibold mb-1.5 flex items-center gap-1.5" style="color: var(--text-secondary)">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    ></path>
                  </svg>
                  {{ translate.comment_moderator }} : <span class="font-normal">{{ comment.moderator }}</span>
                </p>
                <p class="text-xs leading-relaxed" style="color: var(--text-secondary)">
                  <span class="font-medium" style="color: var(--text-primary)"
                    >{{ translate.comment_update }} {{ comment.update }}</span
                  >
                  <br />
                  {{ comment.commentModeration }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="(result !== null && result.nb) === 0" class="mb-3">
        <i class="text-center">{{ translate.no_result }}</i>
      </div>

      <search-paginate
        v-if="result !== null"
        :nb-elements="Number(datas.limit)"
        :current-page="datas.page"
        :nb-elements-total="result.nb"
        :translate="translate.paginate"
        @change-page-event="changePageEvent"
      ></search-paginate>
    </div>
    <div class="flex flex-col gap-6">
      <div class="card rounded-lg overflow-hidden">
        <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
          <h3 class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.statistiques }}</h3>
        </div>
        <div class="p-5 grid grid-cols-3 gap-3">
          <div
            class="flex flex-col items-center justify-center py-3 rounded-xl"
            style="background-color: var(--bg-hover)"
          >
            <span class="text-xl font-bold" style="color: var(--status-validated)">1</span>
            <span class="text-xs mt-0.5 text-center" style="color: var(--text-secondary)">{{
              translate.status_short_validate
            }}</span>
          </div>
          <div
            class="flex flex-col items-center justify-center py-3 rounded-xl"
            style="background-color: var(--bg-hover)"
          >
            <span class="text-xl font-bold" style="color: var(--status-pending)">1</span>
            <span class="text-xs mt-0.5 text-center" style="color: var(--text-secondary)">{{
              translate.status_short_moderate
            }}</span>
          </div>
          <div
            class="flex flex-col items-center justify-center py-3 rounded-xl"
            style="background-color: var(--bg-hover)"
          >
            <span class="text-xl font-bold" style="color: var(--status-moderated)">1</span>
            <span class="text-xs mt-0.5 text-center" style="color: var(--text-secondary)">{{
              translate.status_short_waiting
            }}</span>
          </div>
        </div>
      </div>

      <div class="card rounded-lg overflow-hidden">
        <div class="flex items-center gap-2 px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
          <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            ></path>
          </svg>
          <h3 class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.legend_search }}</h3>
        </div>
        <div class="p-5 flex flex-col gap-4">
          <div class="form-group" style="margin-bottom: 0">
            <label
              class="form-label"
              style="color: var(--text-secondary); font-weight: var(--font-weight-medium); font-size: var(--text-xs)"
              >{{ translate.status_label }}</label
            >
            <select v-model="filters.status" id="list-status" @change="load()" class="form-input">
              <option value="0">{{ translate.status_default }}</option>
              <option v-for="(key, status) in datas.status" :value="status" :selected="status === filters.status">
                {{ key }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label
              class="form-label"
              style="color: var(--text-secondary); font-weight: var(--font-weight-medium); font-size: var(--text-xs)"
              >{{ translate.pages_label }}</label
            >
            <select class="form-input" v-model="filters.pages" id="list-pages" @change="load()">
              <option value="0">{{ translate.pages_default }}</option>
              <option v-for="(key, page) in datas.pages" :value="page" :selected="page === filters.pages">
                {{ key }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <div class="card rounded-lg overflow-hidden">
        <div class="flex items-center gap-2 px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
          <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          <h3 class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.selection_title }}</h3>
        </div>
        <div class="p-5 flex flex-col gap-4">
          <div
            class="rounded-lg p-3.5"
            style="background-color: var(--primary-lighter); border: 1px solid var(--primary-light)"
          >
            <p class="text-xs font-semibold mb-1" style="color: var(--primary)">Commentaires sélectionnés</p>
            <p id="selectedList" class="text-sm font-bold" style="color: var(--text-primary)">
              <span v-if="moderation.selected.length === 0">---</span>
              <span v-else v-for="id in moderation.selected"> #{{ id }},</span>
            </p>
            <p id="selectedSummary" class="text-xs mt-0.5" style="color: var(--text-secondary)">
              {{ moderation.selected.length }} {{ translate.selection_comment }}
            </p>
          </div>

          <div class="form-group" style="margin-bottom: 0">
            <label
              for="list-status"
              class="form-label"
              style="color: var(--text-secondary); font-weight: var(--font-weight-medium); font-size: var(--text-xs)"
              >{{ translate.selection_status }}</label
            >
            <select class="form-input" v-model="moderation.status" id="list-status">
              <option v-for="(key, status) in datas.status" :value="status" :selected="status === filters.status">
                {{ key }}
              </option>
            </select>
          </div>

          <div class="from-group" v-if="moderation.status === '3'">
            <label
              for="moderation-comment"
              class="form-label"
              style="color: var(--text-secondary); font-weight: var(--font-weight-medium); font-size: var(--text-xs)"
              >{{ translate.selection_comment_moderation }}</label
            >
            <textarea
              class="form-input"
              id="moderation-comment"
              rows="2"
              v-model="moderation.moderateComment"
            ></textarea>
          </div>

          <button
            @click="moderateComment()"
            :disabled="moderation.selected.length === 0"
            class="w-full inline-flex items-center justify-center gap-2 btn btn-success btn-sm"
            style="background-color: var(--btn-success)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              ></path>
            </svg>
            {{ translate.selection_submit }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :type="'success'" :show="toasts.toastSuccess.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.toastError.show" @close-toast="closeToast">
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>

  <!--
  <div id="block-moderation" :class="loading === true ? 'block-grid' : ''">
    <div v-if="loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.loading }}</span>
      </div>
    </div>
    <fieldset class="mb-3">
      <legend>{{ translate.legend_search }}</legend>
      <div class="row">
        <div class="col-4">
          <label for="list-status" class="form-label">{{ translate.status_label }}</label>
          <select class="form-select" v-model="filters.status" id="list-status" @change="load()">
            <option value="0">{{ translate.status_default }}</option>
            <option v-for="(key, status) in datas.status" :value="status" :selected="status === filters.status">
              {{ key }}
            </option>
          </select>
        </div>
        <div class="col-4">
          <label for="list-pages" class="form-label">{{ translate.pages_label }}</label>
          <select class="form-select" v-model="filters.pages" id="list-pages" @change="load()">
            <option value="0">{{ translate.pages_default }}</option>
            <option v-for="(key, status) in datas.pages" :value="status" :selected="status === filters.pages">
              {{ key }}
            </option>
          </select>
        </div>
        <div class="col">
          <div class="btn btn-secondary float-end mt-3 me-3" @click="resetSelected()">
            {{ translate.btn_reset }}
          </div>
        </div>
      </div>
    </fieldset>

    <fieldset class="mb-3" v-if="moderation.selected.length > 0">
      <legend>{{ translate.selection_title }}</legend>
      {{ moderation.selected.length }} {{ translate.selection_comment }} :<span v-for="id in moderation.selected">
        #{{ id }},</span
      >

      <div class="row mt-3 mb-3">
        <div class="col">
          <label for="list-status" class="form-label">{{ translate.selection_status }}</label>
          <select class="form-select" v-model="moderation.status" id="list-status">
            <option v-for="(key, status) in datas.status" :value="status" :selected="status === filters.status">
              {{ key }}
            </option>
          </select>
        </div>
        <div class="col">
          <div v-if="moderation.status === '3'">
            <label for="moderation-comment" class="form-label">{{ translate.selection_comment_moderation }}</label>
            <textarea
              class="form-control"
              id="moderation-comment"
              rows="2"
              v-model="moderation.moderateComment"
            ></textarea>
          </div>
        </div>
      </div>
      <div class="btn btn-secondary float-end" @click="moderateComment()">
        {{ translate.selection_submit }}
      </div>
    </fieldset>

    <div v-if="result !== null">
      <div v-for="comment in result.data" :key="comment.id">
        <div class="card mb-3" :class="isChecked(comment.id) ? 'border-2 border-secondary' : ''">
          <div class="card-header">
            <input
              type="checkbox"
              class="form-check-input"
              :id="'comment-' + comment.id"
              @change="updateSelected(comment.id)"
              :checked="isChecked(comment.id)"
            />
            <label class="form-check-label" :for="'comment-' + comment.id"
              >&nbsp; {{ translate.comment_id }} #{{ comment.id }} {{ translate.comment_date }} {{ comment.date }}
              {{ translate.comment_author }} {{ comment.author }}</label
            >
            <div class="float-end" v-html="comment.status"></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-7">
                <h5 class="card-title">{{ translate.comment_comment }}</h5>
                <i>{{ translate.comment_page }} {{ comment.page }}</i>
                <p class="card-text" v-html="renderHtml(comment.comment)"></p>
              </div>
              <div class="col">
                <h5 class="card-title">{{ translate.comment_info }}</h5>
                {{ translate.comment_ip }} : {{ comment.ip }}<br />
                {{ translate.comment_user_agent }} : {{ comment.userAgent }}<br />

                <div v-if="comment.moderator !== null">
                  <fieldset>
                    <legend>{{ translate.comment_moderator }} : {{ comment.moderator }}</legend>
                    <i>{{ translate.comment_update }} {{ comment.update }}</i>
                    {{ comment.commentModeration }}
                  </fieldset>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="result.nb === 0" class="mb-3">
        <i class="text-center">{{ translate.no_result }}</i>
      </div>

      <search-paginate
        :nb-elements="datas.limit"
        :current-page="datas.page"
        :nb-elements-total="result.nb"
        :translate="translate.paginate"
        @change-page-event="changePageEvent"
      ></search-paginate>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccess'"
      :option-class-header="'text-success'"
      :show="toasts.toastSuccess.show"
      @close-toast="closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastError'"
      :option-class-header="'text-danger'"
      :show="toasts.toastError.show"
      @close-toast="closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>-->
</template>

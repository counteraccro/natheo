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

export default defineComponent({
  name: 'CommentModeration',

  components: { SearchPaginate, Toast },

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
      moderation: {
        selected: [],
        status: this.datas.defaultStatus,
        moderateComment: '',
      } as ModerationState,
      filters: {
        status: this.datas.defaultStatus,
        pages: 0,
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
      this.moderation.selected = [];
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
  </div>
</template>

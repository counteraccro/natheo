<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Bloc commentaires : liste paginée, ajout et modération
 */
import { defineComponent, type PropType } from 'vue';
import Pagination from './Pagination.vue';
import { renderMarkdown } from '@/ts/MarkdownEditor/markdownRender';
import { CommentStatus } from '@/utils/Front/Const/CommentStatus';
import type { FrontApi } from '@/utils/Front/FrontApi';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontApiError, FrontCommentTranslate } from '@/ts/Front/NatheoHorizon/Structure.type';
import type { FrontComment } from '@/ts/Front/NatheoHorizon/ContentComment.type';

const AVATAR_COLORS = ['bg-indigo-500', 'bg-emerald-500', 'bg-rose-500', 'bg-sky-500', 'bg-amber-500', 'bg-purple-500'];
const SUCCESS_MESSAGE_DURATION = 3000;

export default defineComponent({
  name: 'ContentComment',
  components: { Pagination },
  props: {
    slug: {
      type: String,
      required: true,
    },
    frontApi: {
      type: Object as PropType<FrontApi>,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontCommentTranslate>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
  },
  emits: {
    'api-failure': (_error: FrontApiError) => true,
  },
  data() {
    return {
      CommentStatus,
      isLoad: false,
      isSaving: false,
      limit: 5,
      page: 1,
      comments: [] as FrontComment[],
      nbElements: 0,
      showAddForm: false,
      moderatingId: null as number | null,
      textModerateComment: '',
      successMessage: '',
      newComment: {
        author: '',
        email: '',
        comment: '',
      },
      validateNewComment: {
        author: true,
        comment: true,
      },
    };
  },
  computed: {
    nbPages(): number {
      return Math.ceil(this.nbElements / this.limit);
    },

    canModerate(): boolean {
      return this.utilsFront.isUserCanModerate();
    },
  },
  mounted() {
    this.loadComment();
  },
  methods: {
    apiFailure(error: FrontApiError): void {
      this.$emit('api-failure', error);
    },

    output(markdown: string): string {
      return renderMarkdown(markdown);
    },

    loadComment(): void {
      this.isLoad = false;
      this.frontApi
        .getCommentByPage({ page_slug: this.slug, locale: this.locale, limit: this.limit, page: this.page })
        .then((response) => {
          this.comments = response.comments;
          this.nbElements = response.rows;
          this.isLoad = true;
        })
        .catch(this.apiFailure);
    },

    changePage(page: number): void {
      this.page = page;
      this.loadComment();
      (this.$refs.top as HTMLElement | undefined)?.scrollIntoView({ behavior: 'smooth' });
    },

    /**
     * Couleur d'avatar stable pour un auteur donné
     */
    pickColor(name: string): string {
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    },

    colorComment(status: number): string {
      if (status === CommentStatus.waitValidation) {
        return 'bg-orange-50/90';
      }
      if (status === CommentStatus.moderate) {
        return 'bg-red-50/90';
      }
      return 'bg-white';
    },

    showSuccess(message: string): void {
      this.successMessage = message;
      setTimeout(() => (this.successMessage = ''), SUCCESS_MESSAGE_DURATION);
    },

    openModerate(id: number): void {
      this.moderatingId = id;
      this.textModerateComment = '';
    },

    moderateComment(id: number, status: number): void {
      const messages: Record<number, string> = {
        [CommentStatus.validate]: this.translate.successValidate,
        [CommentStatus.moderate]: this.translate.successModerate,
        [CommentStatus.waitValidation]: this.translate.successWaiting,
      };

      this.isSaving = true;
      this.frontApi
        .moderateComment(id, { status, moderation_comment: this.textModerateComment })
        .then(() => {
          this.moderatingId = null;
          this.textModerateComment = '';
          this.showSuccess(messages[status]);
          this.loadComment();
        })
        .catch(this.apiFailure)
        .finally(() => (this.isSaving = false));
    },

    addComment(): void {
      this.validateNewComment.author = this.newComment.author.trim() !== '';
      this.validateNewComment.comment = this.newComment.comment.trim() !== '';
      if (!this.validateNewComment.comment || !this.validateNewComment.author) {
        return;
      }

      this.isSaving = true;
      this.frontApi
        .addComment({
          page_slug: this.slug,
          author: this.newComment.author,
          email: this.newComment.email,
          comment: this.newComment.comment,
          user_agent: navigator.userAgent,
        })
        .then(() => {
          this.newComment = { author: '', email: '', comment: '' };
          this.showAddForm = false;
          this.showSuccess(this.translate.formNewCommentSuccessMessage);
          this.loadComment();
        })
        .catch(this.apiFailure)
        .finally(() => (this.isSaving = false));
    },
  },
});
</script>

<template>
  <div v-if="successMessage !== ''" class="text-slate-600 mx-auto max-w-4xl rounded-sm" role="status">
    <p class="text-center text-sm italic">
      {{ successMessage }}
    </p>
  </div>
  <div v-if="isLoad" ref="top" class="mx-auto max-w-4xl p-4 sm:p-6">
    <div
      class="flex flex-wrap items-center justify-between rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm"
    >
      <div class="flex items-center gap-2">
        <svg
          class="size-5 text-neutral-600 dark:text-neutral-300"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
        </svg>
        <h2 class="text-lg font-semibold tracking-tight">{{ translate.title }}</h2>
      </div>
      <div>
        <button
          type="button"
          :aria-expanded="showAddForm"
          aria-controls="block-add-comment"
          class="text-slate-600 font-medium py-2 px-4 rounded-xl hover:bg-theme-4-750 transition border-gray-200 border hover:border-theme-4-750 hover:text-white cursor-pointer mt-3 text-sm md:text-base md:mt-0"
          @click="showAddForm = true"
        >
          {{ translate.btnNewComment }}
        </button>
      </div>
      <span class="text-sm text-slate-600 mt-3 md:mt-0">{{ nbElements }} {{ translate.nbComments }}</span>
    </div>

    <form
      v-if="showAddForm"
      id="block-add-comment"
      class="mx-auto max-w-4xl bg-white rounded-2xl border border-neutral-200/70 p-4 shadow-sm space-y-4 mt-5 animate-fade-in-up"
      novalidate
      @submit.prevent="addComment"
    >
      <div>
        <label for="comment-author" class="block text-sm font-medium text-gray-700 mb-1"
          >{{ translate.formNewCommentPseudoLabel }} *</label
        >
        <input
          id="comment-author"
          v-model="newComment.author"
          type="text"
          name="author"
          :placeholder="translate.formNewCommentPseudoPlaceholder"
          class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring-3 focus:ring-blue-200 outline-none"
          :class="{ 'border-red-500 text-red-600': !validateNewComment.author }"
          :aria-invalid="!validateNewComment.author"
          required
        />
        <p v-if="!validateNewComment.author" class="mt-1 text-sm text-red-600">
          {{ translate.formNewCommentPseudoError }}
        </p>
      </div>
      <div>
        <label for="comment-email" class="block text-sm font-medium text-gray-700 mb-1">{{
          translate.formNewCommentEmailLabel
        }}</label>
        <input
          id="comment-email"
          v-model="newComment.email"
          type="email"
          name="email"
          :placeholder="translate.formNewCommentEmailPlaceholder"
          class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring-3 focus:ring-blue-200 outline-none"
        />
      </div>
      <div>
        <label for="comment-text" class="block text-sm font-medium text-gray-700 mb-1"
          >{{ translate.formNewCommentCommentLabel }} *</label
        >
        <textarea
          id="comment-text"
          v-model="newComment.comment"
          name="comment"
          rows="4"
          :placeholder="translate.formNewCommentCommentPlaceholder"
          class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring-3 focus:ring-blue-200 outline-none"
          :class="{ 'border-red-500 text-red-600': !validateNewComment.comment }"
          :aria-invalid="!validateNewComment.comment"
          required
        ></textarea>
        <p v-if="!validateNewComment.comment" class="mt-1 text-sm text-red-600">
          {{ translate.formNewCommentCommentError }}
        </p>
      </div>

      <div class="flex justify-end space-x-3">
        <button
          type="button"
          class="bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-xl hover:bg-gray-300 transition cursor-pointer"
          @click="showAddForm = false"
        >
          {{ translate.btnNewCommentCancel }}
        </button>
        <button
          type="submit"
          :disabled="isSaving"
          class="text-slate-600 font-medium py-2 px-4 rounded-xl hover:bg-theme-4-750 transition border-gray-200 border hover:border-theme-4-750 hover:text-white cursor-pointer disabled:opacity-50 disabled:cursor-wait"
        >
          {{ translate.btnNewCommentSubmit }}
        </button>
      </div>
    </form>

    <div class="mt-6 space-y-4">
      <article
        v-for="comment in comments"
        :id="'comment-' + comment.id"
        :key="comment.id"
        class="rounded-2xl border border-neutral-200/70 p-4 shadow-sm"
        :class="colorComment(comment.status)"
      >
        <div class="flex gap-3">
          <div
            class="grid size-10 shrink-0 place-items-center rounded-full text-sm font-semibold text-white shadow-sm ring-2 ring-white"
            :class="pickColor(comment.author)"
            aria-hidden="true"
          >
            {{ utilsFront.getInitials(comment.author) }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold">{{ comment.author }}</span>
              <time
                class="text-xs text-slate-600"
                :datetime="new Date(comment.createdAt * 1000).toISOString()"
                :title="utilsFront.formatDate(comment.createdAt * 1000)"
                >• {{ utilsFront.timeAgo(comment.createdAt * 1000) }}</time
              >
            </div>

            <div
              class="mt-2 text-sm leading-relaxed text-slate-600 natheo-content-text"
              v-html="output(comment.comment)"
            ></div>
            <div
              v-if="comment.moderate"
              class="mt-2 text-sm leading-relaxed text-slate-800 italic"
              v-html="output(translate.moderationPrefix + ' ' + comment.moderate)"
            ></div>
          </div>
        </div>

        <template v-if="canModerate">
          <div v-if="!isSaving" class="text-[0.7em] text-right space-x-1">
            <button
              v-if="comment.status !== CommentStatus.validate"
              type="button"
              class="hover:bg-green-600 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1 cursor-pointer"
              @click="moderateComment(comment.id, CommentStatus.validate)"
            >
              {{ translate.validate }}
            </button>
            <button
              v-if="comment.status !== CommentStatus.moderate"
              type="button"
              class="hover:bg-red-600 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1 cursor-pointer"
              @click="openModerate(comment.id)"
            >
              {{ translate.moderate }}
            </button>
            <button
              v-if="comment.status !== CommentStatus.waitValidation"
              type="button"
              class="hover:bg-orange-300 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1 cursor-pointer"
              @click="moderateComment(comment.id, CommentStatus.waitValidation)"
            >
              {{ translate.waiting }}
            </button>
          </div>
          <div v-else class="mt-3 flex gap-3 justify-end">
            <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
            <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
          </div>
        </template>

        <form
          v-if="moderatingId === comment.id"
          class="animate-fade-in-up mb-5"
          @submit.prevent="moderateComment(comment.id, CommentStatus.moderate)"
        >
          <label :for="'moderate-comment-' + comment.id" class="block text-sm font-medium mb-2">{{
            translate.formModerateLabel
          }}</label>
          <textarea
            :id="'moderate-comment-' + comment.id"
            v-model="textModerateComment"
            name="moderation_comment"
            rows="4"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring-3 focus:ring-blue-200 outline-none"
            :placeholder="translate.formModeratePlaceHolder"
          ></textarea>

          <div class="flex justify-end space-x-3 mt-4">
            <button
              type="button"
              class="bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-xl hover:bg-gray-300 transition cursor-pointer"
              @click="moderatingId = null"
            >
              {{ translate.formModerateCancel }}
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="text-slate-600 font-medium py-2 px-4 rounded-xl hover:bg-theme-4-750 transition border-gray-200 border hover:border-theme-4-750 hover:text-white cursor-pointer disabled:opacity-50 disabled:cursor-wait"
            >
              {{ translate.formModerateSubmit }}
            </button>
          </div>
        </form>
      </article>

      <div v-if="nbElements > 0">
        <Pagination :page="page" :nb-pages="nbPages" :translate="translate.pagination" @change="changePage" />
        <div class="flex items-center justify-center mt-2 text-gray-400 text-sm">
          {{ page }} {{ translate.pagination.of }} {{ nbPages }} - {{ nbElements }} {{ translate.nbComments }}
        </div>
      </div>
    </div>
  </div>

  <div v-else>
    <div class="mx-auto max-w-4xl p-4 sm:p-6">
      <div
        class="flex items-center justify-between rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
      >
        <div class="flex items-center gap-2">
          <div class="h-5 w-5 rounded-md bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
          <div class="h-4 w-24 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
        </div>
        <div class="h-4 w-16 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
      </div>

      <div class="mt-6 space-y-4">
        <div
          class="rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
          <div class="flex gap-3">
            <div class="h-10 w-10 shrink-0 rounded-full bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
            <div class="flex-1 space-y-2">
              <div class="flex items-center gap-2">
                <div class="h-4 w-20 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              </div>
              <div class="h-3 w-full rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              <div class="h-3 w-2/3 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              <div class="mt-3 flex gap-3">
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
          <div class="flex gap-3">
            <div class="h-10 w-10 shrink-0 rounded-full bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
            <div class="flex-1 space-y-2">
              <div class="flex items-center gap-2">
                <div class="h-4 w-20 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              </div>
              <div class="h-3 w-full rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              <div class="h-3 w-2/3 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              <div class="mt-3 flex gap-3">
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

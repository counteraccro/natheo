<script>
import {marked} from "marked";
import {CommentStatus} from "../../../../../../utils/Front/Const/CommentStatus";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc commentaire
 */
export default {
  name: 'ContentComment',
  computed: {
    CommentStatus() {
      return CommentStatus
    }
  },
  props: {
    slug: String,
    ajaxRequest: Object,
    translate: Object,
    locale: String,
    utilsFront: Object
  },
  emits: ['api-failure'],
  data() {
    return {
      isLoad: false,
      isLoadModerate: true,
      limit: 5,
      page: 1,
      comments: '',
      nbElements: 0,
      textModerateComment: '',
      msgSuccessModerate: '',
      newComment: {
        author: '',
        email: '',
        comment: ''
      },
      validateNewComment: {
        author: true,
        comment: true
      }
    }
  },
  mounted() {
    this.loadComment()
  },

  methods: {
    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },

    /**
     * Rendu Markdown
     * @param str
     * @returns {*}
     */
    output(str) {
      return marked(str);
    },

    /**
     * Chargement des commentaires
     */
    loadComment() {
      let success = (datas) => {
        this.comments = datas.comments;
        this.nbElements = datas.rows;
      }

      let loader = () => {
        this.isLoad = true
      }
      let params = {
        'page_slug': this.slug,
        'locale': this.locale,
        'limit': this.limit,
        'page': this.page
      };
      this.ajaxRequest.getCommentByPage(params, success, this.apiFailure, loader)
    },

    /**
     * Change de page
     * @param page
     */
    changePage(page) {
      this.page = page;
      this.isLoad = false;
      this.loadComment();
    },

    /**
     * Génère le style des liens de la pagination
     * @param numberPage
     * @param isCentral
     * @param isEnd
     * @returns {string}
     */
    getStylePagePagination(numberPage, isCentral, isEnd) {

      if (numberPage === this.page && isCentral) {
        return "px-3 py-1 rounded-lg border border-gray-300 bg-theme-4-750 !text-theme-1-100 hover:dark:bg-gray-600 transition"
      }

      if (numberPage === this.getNbPage() && isEnd) {
        return "px-3 py-1 rounded-lg border border-gray-100 text-gray-300 transition disabled"
      }

      if (numberPage <= 1 && !isEnd && !isCentral) {
        return "px-3 py-1 rounded-lg border border-gray-100 text-gray-300 transition disabled"
      }

      return "px-3 py-1 rounded-lg border border-gray-300 text-gray-600 hover:bg-theme-4-750 hover:!text-theme-1-100 hover:dark:bg-gray-600 transition"
    },

    /**
     * Retourne le nombre de page
     * @returns {number}
     */
    getNbPage() {
      return Math.ceil(this.nbElements / this.limit);
    },

    /**
     * Génère une couleur random pour les avatar des commentaires
     * @param name
     * @returns {string}
     */
    pickColor(name) {
      const colors = ["bg-indigo-500", "bg-emerald-500", "bg-rose-500", "bg-sky-500", "bg-amber-500", "bg-purple-500"];
      let hash = 0;
      for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return colors[Math.abs(hash) % colors.length];
    },

    /**
     * Couleur de fond des commentaires
     */
    colorComment(status) {
      if (status === CommentStatus.waitValidation) {
        return "bg-orange-50/90";
      }

      if (status === CommentStatus.moderate) {
        return "bg-red-50/90";
      }

      return "bg-white";
    },

    /**
     * Action de modération d'un commentaire
     * @param id
     * @param status
     */
    moderateComment(id, status) {

      this.isLoadModerate = false;
      let success = (datas) => {
      }

      let reset = () => {
        this.msgSuccessModerate = '';
      }

      let loader = () => {

        this.isLoad = false;
        this.loadComment();
        this.isLoadModerate = true

        if (status === CommentStatus.validate) {
          this.msgSuccessModerate = this.translate.successValidate
        }
        if (status === CommentStatus.moderate) {
          this.msgSuccessModerate = this.translate.successModerate
        }
        if (status === CommentStatus.waitValidation) {
          this.msgSuccessModerate = this.translate.successWaiting
        }

        setTimeout(function () {
          reset();
        }, 3000);

      }

      let data = {
        'status': status,
        'moderation_comment': this.textModerateComment
      };

      this.ajaxRequest.putModerate(id, data, success, this.apiFailure, loader);
    },

    /**
     * Affiche ou masque le block de modération
     */
    renderBlock(id, action) {
        let block = document.getElementById(id);
        if(action === 'show') {
          block.classList.remove('hidden');
        } else {
          block.classList.add('hidden');
        }
    },

    /**
     * Permet d'ajouter un commentaire
     */
    addComment() {

      this.validateNewComment.author = this.newComment.author !== '';
      this.validateNewComment.comment = this.newComment.comment !== '';

      if(!this.validateNewComment.comment || !this.validateNewComment.author) {
        return;
      }

      this.isLoad = false;

      let success = (datas) => {
      }

      let reset = () => {
        this.msgSuccessModerate = '';
      }

      let loader = () => {

        this.newComment.comment = '';
        this.newComment.email = '';
        this.newComment.author = '';

        this.loadComment();
        this.msgSuccessModerate = this.translate.formNewCommentSuccessMessage;
        setTimeout(function () {
          reset();
        }, 3000);
      }

      let data = {
        'page_slug' : this.slug,
        'author' : this.newComment.author,
        'email' : this.newComment.email,
        'comment' : this.newComment.comment,
        'ip' : this.utilsFront.getIp(),
        'user_agent': navigator.userAgent
      };

      this.ajaxRequest.addComment(data, success, this.apiFailure, loader);

    }
  }
}
</script>

<template>

  <div v-if="msgSuccessModerate !== ''" class="text-slate-600 mx-auto max-w-4xl rounded-sm">
    <p class="text-center text-sm italic">
      {{ this.msgSuccessModerate }}
    </p>
  </div>
  <div v-if="this.isLoad" class="mx-auto max-w-4xl p-4 sm:p-6" id="ancre-comment">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="size-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <h2 class="text-lg font-semibold tracking-tight">{{ this.translate.title }}</h2>
      </div>
      <div>

        <button type="button" @click="this.renderBlock('block-add-comment', 'show')"
                class="text-slate-600 font-medium py-2 px-4 rounded-xl hover:bg-theme-4-750 transition border-gray-200 border-1 hover:border-theme-4-750 hover:text-white cursor-pointer mt-3 text-sm md:text-base md:mt-0">
          {{ this.translate.btnNewComment }}
        </button>

      </div>
      <span class="text-sm text-slate-600 mt-3 md:mt-0">{{ this.nbElements }} {{
          this.translate.nbComments
        }}</span>
    </div>


    <div id="block-add-comment" class="mx-auto max-w-4xl bg-white rounded-2xl border border-neutral-200/70 p-4 shadow-sm space-y-4 mt-5 opacity-0 animate-fadeInUp hidden">
        <div>
          <label for="pseudo" class="block text-sm font-medium text-gray-700 mb-1">{{ this.translate.formNewCommentPseudoLabel }} *</label>
          <input
              type="text"  id="pseudo"  name="pseudo" v-model="this.newComment.author"
              :placeholder="this.translate.formNewCommentPseudoPlaceholder"
              class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring focus:ring-blue-200 outline-none" :class="!this.validateNewComment.author ? 'invalid:border-red-500 invalid:text-red-600' : ''"  required
          />
          <p v-if="!this.validateNewComment.author" class="mt-1 text-sm text-red-600">
            {{ this.translate.formNewCommentPseudoError}}
          </p>
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ this.translate.formNewCommentEmailLabel }}</label>
          <input
              type="email" id="email" name="email" v-model="this.newComment.email"
              :placeholder="this.translate.formNewCommentEmailPlaceholder"
              class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring focus:ring-blue-200 outline-none"
          />
        </div>
        <div>
          <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-1">{{ this.translate.formNewCommentCommentLabel }} *</label>
          <textarea
              id="commentaire" name="commentaire" rows="4" :placeholder="this.translate.formNewCommentCommentPlaceholder" v-model="this.newComment.comment"
              class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring focus:ring-blue-200 outline-none" :class="!this.validateNewComment.comment ? 'invalid:border-red-500 invalid:text-red-600' : ''"
              required
          ></textarea>
          <p v-if="!this.validateNewComment.comment" class="mt-1 text-sm text-red-600">
            {{ this.translate.formNewCommentCommentError}}
          </p>
        </div>

        <div class="flex justify-end space-x-3">
          <button type="button" @click="this.renderBlock('block-add-comment', 'remove')"
                  class="bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-xl hover:bg-gray-300 transition cursor-pointer">
            {{ this.translate.btnNewCommentCancel }}
          </button>
          <button type="submit" @click="this.addComment()"
                  class="text-slate-600 font-medium py-2 px-4 rounded-xl hover:bg-theme-4-750 transition border-gray-200 border-1 hover:border-theme-4-750 hover:text-white cursor-pointer">
            {{ this.translate.btnNewCommentSubmit }}
          </button>
        </div>
      </div>

    <div class="mt-6 space-y-4">

      <div v-for="comment in this.comments" :id="'comment-' + comment.id"
           class="rounded-2xl border border-neutral-200/70 p-4 shadow-sm" :class="this.colorComment(comment.status)">
        <div class="flex gap-3">
          <div
              class="grid size-10 place-items-center rounded-full text-sm font-semibold text-white shadow-sm ring-2 ring-white"
              :class="this.pickColor(comment.author)">
            {{ this.utilsFront.getInitials(comment.author) }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold">{{ comment.author }}</span>
              <span class="text-xs text-slate-600">• {{
                  this.utilsFront.timeAgo(comment.createdAt * 1000)
                }}</span>
            </div>

            <div class="mt-2 text-sm leading-relaxed text-slate-600 natheo-content-text"
                 v-html="this.output(comment.comment)">
            </div>
            <div v-if="comment.moderate" class="mt-2 text-sm leading-relaxed text-slate-800 italic"
                 v-html="this.output('Modération : ' + comment.moderate)">
            </div>
          </div>
        </div>
        <div class="text-[0.7em] text-right" v-if="this.utilsFront.isUserCanModerate() && this.isLoadModerate">
          <a :href="'#comment-' + comment.id" @click="this.moderateComment(comment.id, CommentStatus.validate)"
             class="hover:bg-green-600 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1"
             v-if="comment.status !== CommentStatus.validate">{{ this.translate.validate }}</a>
          <a :href="'#comment-' + comment.id" @click="this.renderBlock('block-moderate-comment-' + comment.id, 'show')"
             class="hover:bg-red-600 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1"
             v-if="comment.status !== CommentStatus.moderate">{{ this.translate.moderate }}</a>
          <a :href="'#comment-' + comment.id" @click="this.moderateComment(comment.id, CommentStatus.waitValidation)"
             class="hover:bg-orange-300 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1"
             v-if="comment.status !== CommentStatus.waitValidation">{{ this.translate.waiting }}</a>
        </div>
        <div v-else-if="this.utilsFront.isUserCanModerate() && !this.isLoadModerate">
          <div class="mt-3 flex gap-3 justify-end">
            <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
            <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
          </div>
        </div>

        <div :id="'block-moderate-comment-' + comment.id" class="opacity-0 translate-y-4 animate-fadeInUp mb-5 hidden">
          <!-- Zone de texte -->
          <label for="comment" class="block text-sm font-medium mb-2">{{ this.translate.formModerateLabel }}</label>
          <textarea id="comment" name="comment" rows="4"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:border-theme-4-750 focus:ring focus:ring-blue-200 outline-none"
                    :placeholder="this.translate.formModeratePlaceHolder" v-model="this.textModerateComment"></textarea>

          <!-- Bouton -->
          <div class="flex justify-end space-x-3 mt-4">
            <button type="button" @click="this.renderBlock('block-moderate-comment-' + comment.id,'remove')"
                    class="bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-xl hover:bg-gray-300 transition cursor-pointer">
              {{ this.translate.formModerateCancel }}
            </button>
            <button type="submit" @click="this.moderateComment(comment.id, CommentStatus.moderate)"
                    class="text-slate-600 font-medium py-2 px-4 rounded-xl hover:bg-theme-4-750 transition border-gray-200 border-1 hover:border-theme-4-750 hover:text-white cursor-pointer">
              {{ this.translate.formModerateSubmit }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="this.nbElements > 0">
        <nav class="content-paginate flex flex-wrap gap-y-3 items-center justify-center mt-8 space-x-2" aria-label="Pagination">
          <a href="#ancre-comment" @click="this.changePage(1)" class="text-sm "
             :class="this.getStylePagePagination(this.page, false, false)"><<</a>
          <a href="#ancre-comment" @click="this.changePage(this.page - 1)" class="text-sm"
             :class="this.getStylePagePagination(this.page, false,false)"><</a>

          <div v-for="(n, i) in this.getNbPage()" :id="'p-comment-' + n">
          <a href="#ancre-comment" :class="this.getStylePagePagination(n, true,false)" @click="this.changePage(n)" v-if="n === this.page-1 || n === this.page+1 || n === this.page || n <= 2 || n >= this.getNbPage()-1">{{
              n
            }}</a>
            <a href="#ancre-comment" :class="this.getStylePagePagination(n, true,false)" @click="this.changePage(n)" v-else-if="n === this.page-2 || n === this.page+2">...</a>
          </div>

          <a href="#ancre-comment" @click="this.changePage(this.page + 1)" class="text-sm"
             :class="this.getStylePagePagination(this.page, false,true)">></a>
          <a href="#ancre-comment" @click="this.changePage(this.getNbPage())" class="text-sm"
             :class="this.getStylePagePagination(this.page, false,true)">>></a>
        </nav>
        <div class="flex items-center justify-center mt-2 text-gray-400 text-sm">
          {{ this.page }} sur {{ this.getNbPage() }} - {{ this.nbElements }} {{ this.translate.nbComments }}
        </div>
      </div>

    </div>
  </div>

  <div v-else>
    <div class="mx-auto max-w-4xl p-4 sm:p-6">
      <div
          class="flex items-center justify-between rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center gap-2">
          <div class="h-5 w-5 rounded-md bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
          <div class="h-4 w-24 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
        </div>
        <div class="h-4 w-16 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
      </div>

      <div class="mt-6 space-y-4">
        <div
            class="rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
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
            class="rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
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

<style>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out forwards;
}
</style>
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
      limit: 10,
      page: 1,
      comments: '',
      nbElements: 0,
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
    }
  }
}
</script>

<template>

  <div v-if="this.isLoad" class="mx-auto max-w-4xl p-4 sm:p-6" id="ancre-comment">
    <!-- Header -->
    <div
        class="flex items-center justify-between rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="size-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <h2 class="text-lg font-semibold tracking-tight">{{ this.translate.title }}</h2>
      </div>
      <span class="text-sm text-slate-600">{{ this.nbElements }} {{
          this.translate.nbComments
        }}</span>
    </div>

    <div class="mt-6 space-y-4">

      <div v-for="comment in this.comments"
           class="rounded-2xl border border-neutral-200/70 p-4 shadow-sm" :class="this.colorComment(comment.status)">
        <div class="flex gap-3">
          <div
              class="grid size-10 place-items-center rounded-full text-sm font-semibold text-white shadow-sm ring-2 ring-white "
              :class="this.pickColor(comment.author)">
            {{ this.utilsFront.getInitials(comment.author) }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold">{{ comment.author }}</span>
              <span class="text-xs text-slate-600">• {{
                  this.utilsFront.timeAgo(comment.createdAt * 1000)
                }}</span>
              <div class="text-xs" v-if="this.utilsFront.isUserCanModerate()"> aaa</div>
            </div>

            <div class="mt-2 text-sm leading-relaxed text-slate-600"
                 v-html="this.output(comment.comment)">
            </div>
            <!--<div class="mt-3 flex gap-3 text-xs">
              <button class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">
                ❤️ 2 J’aime
              </button>
              <button class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">
                ↩️ Répondre
              </button>
            </div> -->
          </div>
        </div>
      </div>

      <div v-if="this.nbElements > 0">
        <nav class="content-paginate flex items-center justify-center mt-8 space-x-2" aria-label="Pagination">
          <a href="#ancre-comment" @click="this.changePage(1)"
             :class="this.getStylePagePagination(this.page, false, false)"><<</a>
          <a href="#ancre-comment" @click="this.changePage(this.page - 1)"
             :class="this.getStylePagePagination(this.page, false,false)"><</a>

          <a href="#ancre-comment" v-for="(n, i) in this.getNbPage()"
             :class="this.getStylePagePagination(n, true,false)" @click="this.changePage(n)">
          <span v-if="n === this.page-1 || n === this.page+1 || n === this.page || n <= 2 || n >= this.getNbPage()-1">{{
              n
            }}</span>
            <span v-else-if="n === this.page-2 || n === this.page+2">...</span>
          </a>

          <a href="#ancre-comment" @click="this.changePage(this.page + 1)"
             :class="this.getStylePagePagination(this.page, false,true)">></a>
          <a href="#ancre-comment" @click="this.changePage(this.getNbPage())"
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
      <!-- Header Skeleton -->
      <div
          class="flex items-center justify-between rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center gap-2">
          <div class="h-5 w-5 rounded-md bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
          <div class="h-4 w-24 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
        </div>
        <div class="h-4 w-16 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
      </div>

      <!-- Liste skeleton -->
      <div class="mt-6 space-y-4">

        <!-- Commentaire skeleton -->
        <div
            class="rounded-2xl border border-neutral-200/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
          <div class="flex gap-3">
            <!-- Avatar -->
            <div class="h-10 w-10 shrink-0 rounded-full bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>

            <!-- Contenu -->
            <div class="flex-1 space-y-2">
              <div class="flex items-center gap-2">
                <div class="h-4 w-20 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              </div>
              <div class="h-3 w-full rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              <div class="h-3 w-2/3 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>

              <!-- Actions -->
              <div class="mt-3 flex gap-3">
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
                <div class="h-3 w-12 rounded bg-neutral-200 animate-pulse dark:bg-neutral-700"></div>
              </div>

            </div>
          </div>
        </div>

        <!-- Deuxième commentaire skeleton -->
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
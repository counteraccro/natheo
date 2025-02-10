<script>
/**
 * Permet de modérer 1 commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import SearchPaginate from "../../../../Components/Global/Search/SearchPaginate.vue";
import {marked} from "marked";

export default {
  name: 'CommentModeration',
  components: {SearchPaginate, Toast},
  props: {
    urls: Object,
    translate: Object,
    datas: Object
  },
  emits: [],
  mounted() {
    this.load();
  },
  data() {
    return {
      loading: false,
      result: null,
      selected: [],
      filters: {
        status : this.datas.defaultStatus,
        pages : 0
      },
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      },
    }
  },
  methods: {

    /**
     * Charge la liste des commentaires
     */
    load() {
      this.loading = true;
      axios.get(this.urls.filter + '/' + this.filters.status + '/' + this.filters.pages + '/' + this.datas.page + '/' + this.datas.limit).then((response) => {
        this.result = response.data;
        console.log(this.result);
        console.log(this.result.data.length)
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    changePageEvent(page, limit)
    {
      this.datas.page = page;
      this.datas.limit = limit;
      this.load();
    },

    updateSelected(id)
    {
      if(this.selected.find((element) => element === id))
      {
        this.selected = this.selected.filter(function(item) {
          return item !== id
        })
      }
      else {
        this.selected.push(id);
      }
    },

    /**
     * Converti du markdown en html
     * @param value
     * @return {*}
     */
    renderHtml(value)
    {
      return marked(value);
    },


    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },
  }
}
</script>

<template>

  <div id="block-moderation" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>
    <fieldset class="mb-3">
      <legend>{{ this.translate.legend_search }}</legend>
      <div class="row">
        <div class="col-4">
          <label for="list-status" class="form-label">{{ this.translate.status_label }}</label>
          <select class="form-select" v-model="this.filters.status" id="list-status" @change="this.load()">
            <option value="0">{{ this.translate.status_default }}</option>
            <option v-for="(key, status) in this.datas.status" :value="status" :selected="status === this.filters.status">{{ key }}</option>
          </select>
        </div>
        <div class="col-4">
          <label for="list-pages" class="form-label">{{ this.translate.pages_label }}</label>
          <select class="form-select" v-model="this.filters.pages" id="list-pages" @change="this.load()">
            <option value="0">{{ this.translate.pages_default }}</option>
            <option v-for="(key, status) in this.datas.pages" :value="status" :selected="status === this.filters.pages">{{ key }}</option>
          </select>
        </div>
        <div class="col"></div>
      </div>
    </fieldset>

    <fieldset class="mb-3" v-if="this.selected.length > 0">
      <legend>{{ this.translate.selection_title }}</legend>
      {{ this.translate.selection_comment }} :<span v-for="id in this.selected"> #{{ id }},</span>

    </fieldset>

    <div v-if="this.result !== null">

      <div v-for="comment in this.result.data">
        <div class="card mb-3">
          <div class="card-header">
            <input type="checkbox" class="form-check-input" :id="'comment-' + comment.id" @change="updateSelected(comment.id)" />
            <label class="form-check-label" :for="'comment-' + comment.id">&nbsp;
            {{ this.translate.comment_id }} #{{ comment.id }} {{ this.translate.comment_date }}
            {{ comment.date }} {{ this.translate.comment_author }} {{ comment.author }}</label>
            <div class="float-end" v-html="comment.status">

            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-7">
                <h5 class="card-title">{{ this.translate.comment_comment }}</h5>
                <i>{{ this.translate.comment_page }} {{ comment.page }}</i>
                <p class="card-text" v-html="this.renderHtml(comment.comment)"></p>
              </div>
              <div class="col">
                <h5 class="card-title">{{ this.translate.comment_info }}</h5>
                {{ this.translate.comment_ip }} : {{ comment.ip }}<br />
                {{ this.translate.comment_user_agent }} : {{ comment.userAgent }}<br />

                <div v-if="comment.moderator !== null">
                  <fieldset>
                    <legend>{{ this.translate.comment_moderator }} : {{ comment.moderator }}</legend>
                    <i>{{ this.translate.comment_update }} {{ comment.update }}</i>
                    {{ comment.commentModeration }}
                  </fieldset>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <search-paginate
          :nb-elements="this.datas.limit"
          :current-page="this.datas.page"
          :nb-elements-total="this.result.nb"
          :translate="this.translate.paginate"
          @change-page-event="changePageEvent"
      ></search-paginate>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastError'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastError.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>

</template>
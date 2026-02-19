<script>
/**
 * Permet de modérer 1 commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from 'axios';
import MarkdownEditor from '../../../../Components/Global/MarkdownEditor/MarkdownEditor.vue';
import Toast from '../../../../Components/Global/Toast.vue';
import { emitter } from '../../../../../utils/useEvent';

export default {
  name: 'CommentEdit',
  components: { Toast, MarkdownEditor },
  props: {
    urls: Object,
    translate: Object,
    datas: Object,
  },
  emits: [],
  mounted() {
    this.load();
  },
  data() {
    return {
      loading: false,
      comment: null,
      toasts: {
        toastSuccessComment: {
          show: false,
          msg: '',
        },
        toastErrorComment: {
          show: false,
          msg: '',
        },
      },
    };
  },
  methods: {
    /**
     * Charge les données du commentaire
     */
    load() {
      this.loading = true;
      axios
        .get(this.urls.load_comment + '/' + this.datas.id)
        .then((response) => {
          this.comment = response.data.comment;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Met à jour le commentaire
     */
    updateValue(id, value) {
      this.comment.comment = value;
    },

    /**
     * Mise à jour du commentaire
     */
    save() {
      this.loading = true;
      axios
        .put(this.urls.save, {
          comment: this.comment,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccessComment.msg = response.data.msg;
            this.toasts.toastSuccessComment.show = true;
            emitter.emit('reset-check-confirm');
          } else {
            this.toasts.toastErrorComment.msg = response.data.msg;
            this.toasts.toastErrorComment.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.load();
        });
    },

    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },
  },
};
</script>

<template>
  <div id="block-faq" class="h-50" :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div v-if="this.comment" class="comment">
      <fieldset class="mb-4">
        <legend>{{ this.translate.titleInfo }}</legend>
        <div class="row">
          <div class="col-6">
            <div>
              <span v-html="this.comment.statusStr"></span>
            </div>
            <div v-if="this.comment.status == this.datas.statusModerate" class="mt-2">
              <b>{{ this.translate.moderationComment }}</b> : <br />
              <textarea
                class="form-control"
                id="moderation-content"
                rows="3"
                v-model="this.comment.moderationComment"
              ></textarea>
              <div v-if="this.comment.userModeration">
                {{ this.translate.moderationAuthor }} : {{ this.comment.userModeration.login }}
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="float-end">
              {{ this.translate.author }} : {{ this.comment.author }} ({{ this.comment.email }})<br />
              {{ this.translate.created }} {{ this.comment.createdAt }}<br />
              {{ this.translate.ip }} : {{ this.comment.ip }}<br />
              {{ this.translate.userAgent }} : {{ this.comment.userAgent }}<br />
            </div>
          </div>
        </div>
      </fieldset>

      <div class="row mb-4">
        <div class="col-8"></div>
        <div class="col-4">
          <select class="form-select" v-model="this.comment.status">
            <option
              v-for="(key, status) in this.datas.status"
              :value="status"
              :selected="status === this.comment.status"
            >
              {{ key }}
            </option>
          </select>
        </div>
      </div>

      <MarkdownEditor
        :me-id="'' + this.comment.id + ''"
        :me-value="this.comment.comment"
        :me-translate="this.translate.markdown"
        :me-key-words="[]"
        :me-rows="16"
        :me-save="true"
        :me-preview="false"
        @editor-value="this.updateValue"
      >
      </MarkdownEditor>

      <div class="btn btn-secondary float-end" :class="this.loading ? 'disabled' : ''" @click="this.save">
        {{ this.translate.btnEdit }}
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast
      :id="'toastSuccessComment'"
      :option-class-header="'text-success'"
      :show="this.toasts.toastSuccessComment.show"
      @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccessComment.msg"></div>
      </template>
    </toast>

    <toast
      :id="'toastErrorComment'"
      :option-class-header="'text-danger'"
      :show="this.toasts.toastErrorComment.show"
      @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastErrorComment.msg"></div>
      </template>
    </toast>
  </div>
</template>

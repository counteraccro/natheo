<script>
/**
 * Permet de modérer 1 commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import {emitter} from "../../../../../utils/useEvent";

export default {
  name: 'CommentModeration',
  components: {Toast},
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
        console.log('ici');
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
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

  <div id="block-moderation">
    <fieldset>
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

    <fieldset class="mt-3 mb-3">
      <legend>{{ this.translate.selection_title }}</legend>
    </fieldset>
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
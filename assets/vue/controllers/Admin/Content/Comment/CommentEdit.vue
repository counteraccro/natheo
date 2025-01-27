<script>/**
 * Permet de modérer 1 commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import MarkdownEditor from "../../../../Components/Global/MarkdownEditor.vue";

export default {
  name: 'CommentEdit',
  components: {MarkdownEditor},
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
      comment: null,
    }
  },
  methods: {

    /**
     * Charge les données du commentaire
     */
    load() {
      this.loading = true;
      axios.get(this.urls.load_comment + '/' + this.datas.id).then((response) => {
        this.comment = response.data.comment;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Met à jour le commentaire
     */
    updateValue(id, value) {
      console.log(value);
    }

  }
}
</script>

<template>

  <div id="block-faq" class="h-50" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
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
            <div v-if="comment.moderationComment" class="mt-2">
              <b>{{ this.translate.moderationComment }}</b> : <br />
              {{ comment.moderationComment }}
            </div>
          </div>
          <div class="col-6">
            <div class="float-end">
              {{ this.translate.author }} : {{ this.comment.author }} ({{ this.comment.email }})<br/>
              {{ this.translate.created }} {{ this.comment.createdAt }}<br/>
              {{ this.translate.ip }} : {{ this.comment.ip }}<br/>
              {{ this.translate.userAgent }} : {{ this.comment.userAgent }}<br/>
            </div>
          </div>
        </div>
      </fieldset>

      <div class="row mb-4">
        <div class="col-8">
        </div>
        <div class="col-4">
          <select class="form-select" v-model="this.comment.status">
            <option v-for="(key, status) in this.datas.status" :value="status" :selected="status === this.comment.status">{{ key }}</option>
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

    </div>
  </div>
</template>

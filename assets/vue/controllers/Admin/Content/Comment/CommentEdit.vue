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
    id: Number
  },
  emits: [],
  mounted() {
    console.log(this.translate.markdown)
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
      axios.get(this.urls.load_comment + '/' + this.id).then((response) => {
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
    updateValue(id, value)
    {
        console.log(value);
    }

  }
}
</script>

<template>

  <div id="block-faq" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div v-if="this.comment" class="comment">

      <MarkdownEditor
          :me-id="this.comment.id"
          :me-value="this.comment.comment"
          :me-translate="this.translate.markdown"
          :me-key-words="[]"
          :me-rows="16"
          :me-save="true"
          :me-preview="false"
          @editor-value="this.updateValue"
      >

      </MarkdownEditor>

      {{ this.translate.loading }}
    </div>
  </div>
</template>

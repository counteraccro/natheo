<script>
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
      limit: 20,
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

    loadComment() {
      let success = (datas) => {
        this.faq = datas
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
  }
}
</script>

<template>
  Comment module
</template>
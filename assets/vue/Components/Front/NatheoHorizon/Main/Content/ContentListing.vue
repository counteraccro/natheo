<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu de type listing
 */
export default {
  name: 'ContentLinsting',
  props: {
    data: Object,
    utilsFront: Object,
    locale: String,
    ajaxRequest: Object,
  },
  emits: ['api-failure'],
  data() {
    return {
      content: '',
    }
  },
  created() {
    this.loadContent();
  },
  mounted() {

  },
  computed: {
    output() {
      return marked(this.content);
    },
  },
  methods: {
    loadContent() {
      let success = (datas) => {
        console.log(datas)
      }

      let loader = () => {

      }
      let params = {
        'id': this.data.id,
        'locale': this.locale
      };
      this.ajaxRequest.getContentPage(params, success, this.apiFailure, loader)
    },

    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },
  }
}
</script>

<template>
  content listing
</template>
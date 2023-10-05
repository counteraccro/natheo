<script>/**
 * Permet d'ajouter ou éditer une page
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PageForm',
  props: {
    url: String,
    translate: Object,
    locales: Object,
    pPage: Object
  },
  emits: [],
  data() {
    return {
      page: this.pPage
    }
  },
  mounted() {

  },
  computed: {},
  methods: {

  }
}


</script>

<template>
  <div>Template Page</div>

  <div v-for="pTrans in this.page.pageTranslations">
    <input type="text" v-model="pTrans.locale">
  </div>

  <div v-for="pTrans in this.page.pageTranslations">
    <div>{{pTrans.locale}}</div>
  </div>

</template>
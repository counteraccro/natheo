<script xmlns:auto-complete="http://www.w3.org/1999/html">

/**
 * Permet de récupérer un tag pour le lier à autre chose
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import AutoComplete from "./AutoComplete.vue";

export default {
  name: 'Tag',
  components: {
    AutoComplete
  },
  props: {
    urls: Object,
  },
  emits: ['add-tag', 'remove-tag'],
  data() {
    return {
      translate: [],
      resultSearch: [],
      component: '',
    }
  },
  mounted() {
    this.loadData();
  },
  computed: {},
  methods: {

    loadData() {
      axios.post(this.urls.init_data, {
      }).then((response) => {
        this.translate = response.data.translate
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.component = 'AutoComplete';
      });
    }

  }
}
</script>

<template>
  <div id="tag-component">
    <h5>{{ this.translate.title }}</h5>

    <component :is="this.component"
        :url="''"
        :translate="this.translate.auto_complete"
    />
  </div>

</template>

<style scoped>

</style>
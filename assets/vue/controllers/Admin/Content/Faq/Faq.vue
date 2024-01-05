<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";

export default {
  name: "Faq",
  components: {},
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    id: Number
  },
  data() {
    return {
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {

    /**
     * Chargement des données
     */
    loadData() {
      /*axios.post(this.url_select, {
        'time': this.time
      }).then((response) => {
        this.select = response.data.files;
        this.trans = response.data.trans;
      }).catch((error) => {
        console.log(error);
      }).finally();*/
    },
  }
}
</script>

<template>
  <div>Template FAQ</div>

</template>
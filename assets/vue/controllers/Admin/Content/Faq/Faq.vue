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
    this.loadFaq();
  },
  methods: {

    /**
     * Chargement des données FAQ
     */
    loadFaq() {
      axios.post(this.urls.load_faq, {
        'id': this.id
      }).then((response) => {
        console.log(response.data);
      }).catch((error) => {
        console.log(error);
      }).finally();
    },
  }
}
</script>

<template>
  <div>Template FAQ</div>

</template>
<script>
/**
 * Contenu de l'onglet historique
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PageHistory',
  props: {
    history: Object,
    translate: Object,
  },
  emits: ['load-page-history'],
  data() {
    return {
    }
  },
  mounted() {

  },
  computed: {},
  methods: {
    autoSave()
    {
      //this.$emit('auto-save', this.page);
    }
  }
}
</script>

<template>

  <h5>{{ this.translate.title }}</h5>
  <div>{{ this.translate.description }}</div>
  <div v-if="this.history.length > 0" v-for="history in this.history">
     <div> {{ history.time }} {{ history.user }}</div>
  </div>
  <div v-else class="text-center">
  <hr/>
    <i>{{ this.translate.empty }}</i>
  </div>

</template>
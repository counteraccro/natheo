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

  <div>{{ this.translate.description }}</div>
  <div v-for="history in this.history">
      {{ history.time }}
  </div>

</template>
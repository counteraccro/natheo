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
  emits: ['reload-page-history'],
  data() {
    return {}
  },
  mounted() {

  },
  computed: {},
  methods: {}
}
</script>

<template>

  <div id="content-history">
    <h5>{{ this.translate.title }}</h5>
    <div>{{ this.translate.description }}</div>
    <hr/>
    <div v-if="this.history.length > 0" v-for="history in this.history">
      <div class="m-0 ms-0 p-2 row-history">
        {{ this.translate.update }}#{{ history.id }},
        <span v-html="history.time"></span>,
        {{ this.translate.for }} {{ history.user }}

        <div class="float-end" style="cursor: pointer" @click="$emit('reload-page-history', history.id)">
          <div class="icon-link icon-link-hover"><i class="bi bi-arrow-clockwise"></i> {{ this.translate.reload }}</div>
        </div>
      </div>
    </div>
    <div v-else class="text-center">
      <i>{{ this.translate.empty }}</i>
    </div>
  </div>

</template>
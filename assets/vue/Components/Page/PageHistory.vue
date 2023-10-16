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
  methods: {}
}
</script>

<template>

  <h5>{{ this.translate.title }}</h5>
  <div>{{ this.translate.description }}</div>
  <div v-if="this.history.length > 0" v-for="history in this.history">
     <div class="m-2 ms-0">
       {{ this.translate.update }}#{{ history.id }},
       <span v-html="history.time"></span>,
       {{ this.translate.for }} {{ history.user }}

       <div class="float-end" style="cursor: pointer" @click="$emit('load-page-history', history.id)">
         <div class="icon-link icon-link-hover"><i class="bi bi-arrow-clockwise"></i> {{ this.translate.reload }}</div>
       </div>
     </div>
  </div>
  <div v-else class="text-center">
  <hr/>
    <i>{{ this.translate.empty }}</i>
  </div>

</template>
<script>/**
 * Permet d'afficher une FAQ en preview
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {marked} from "marked";


export default {
  name: 'PreviewFaq',
  props: {
    pContent: Object,
  },
  emits: [],
  data() {
    return {}
  },
  methods: {
    getDate(time) {
      let date = new Date(time * 1000);
      return date.toLocaleString();
    },

    getMarked(value) {
      return marked(value)
    }
  }

}
</script>

<template>
  <div class="block-faq">
    <h4>{{ this.pContent.content.title }}</h4>
    <div class="card mt-2" v-for="(faq, index) in this.pContent.content.categories">
      <div class="card-header" data-bs-toggle="collapse" :href="'#bloc-' + index" aria-expanded="false">
        <p class="card-title">{{ faq.title}}</p>
      </div>
      <div class="collapse multi-collapse" :id="'bloc-' + index">
        <div class="card-body">
         <div v-for="q in faq.questions">
           <p>{{ q.title }}</p>
           <p v-html="this.getMarked(q.answer)"></p>
         </div>
        </div>
      </div>
    </div>

  </div>
</template>
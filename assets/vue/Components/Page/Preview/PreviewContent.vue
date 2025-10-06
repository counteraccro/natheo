<script>
/**
 * Affiche un texte au format markdown
 * @author Gourdon Aymeric
 * @version 1.0
 */
import { marked } from 'marked';
import PreviewListing from './PreviewListing.vue';
import PreviewFaq from './PreviewFaq.vue';

export default {
  name: 'PreviewContent',
  components: { PreviewFaq, PreviewListing },
  props: {
    pContent: Object,
    translate: Object,
  },
  emits: [],
  data() {
    return {};
  },
  methods: {
    getMarked(value) {
      return marked(value);
    },
  },
};
</script>

<template>
  <div v-if="this.pContent.type === 1">
    <div class="border-light border-1 border p-2 rounded" style="min-height: 50px; background-color: #ffffff">
      <div v-html="this.getMarked(this.pContent.content)" v-if="this.pContent.content !== undefined"></div>
      <div v-else>
        <p class="placeholder-glow">
          <span class="placeholder col-6"></span>
          <span class="placeholder col-8"></span>
          <span class="placeholder col-12"></span>
        </p>
      </div>
    </div>
  </div>
  <div v-if="this.pContent.type === 2">
    <div class="border-light border-1 border p-2 rounded" style="min-height: 50px; background-color: #ffffff">
      <PreviewFaq :p-content="this.pContent" v-if="this.pContent.content !== undefined"></PreviewFaq>
      <div v-else class="placeholder-glow">
        <h4 class="placeholder col-12"></h4>
        <div class="card">
          <div class="card-header placeholder"></div>
          <div class="card-body">
            <h5 class="card-title placeholder col-12"></h5>
            <p class="card-text placeholder col-12"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-if="this.pContent.type === 3">
    <div class="border-light border-1 border p-2 rounded" style="min-height: 50px; background-color: #ffffff">
      <PreviewListing :p-content="this.pContent" v-if="this.pContent.content !== undefined"></PreviewListing>
      <div v-else>
        <ul>
          <li>
            <span class="placeholder-glow">
              <span class="placeholder" style="width: 25%"></span>
              <span class="placeholder float-end" style="width: 10%"></span>
            </span>
          </li>
          <li>
            <span class="placeholder-glow">
              <span class="placeholder" style="width: 25%"></span>
              <span class="placeholder float-end" style="width: 10%"></span>
            </span>
          </li>
          <li>
            <span class="placeholder-glow">
              <span class="placeholder" style="width: 25%"></span>
              <span class="placeholder float-end" style="width: 10%"></span>
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Affiche un texte au format markdown
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {marked} from "marked";
import PreviewListing from "./PreviewListing.vue";


export default {
  name: 'PreviewContent',
  components: {PreviewListing},
  props: {
    pContent: Object,
    translate: Object
  },
  emits: [],
  data() {
    return {}
  },
  methods: {
    getMarked(value) {
      return marked(value)
    }
  }
}
</script>

<template>
  <div v-if="this.pContent.type === 1">
    <div class="border-light border-1 border p-2 rounded" style="min-height: 50px;"
        v-html="this.getMarked(this.pContent.content)"
        v-if="this.pContent.content !== undefined"></div>
    <div v-else>
      {{ this.translate.loading_text }}
    </div>
  </div>
  <div v-if="this.pContent.type === 2">
    <div class="border-light border-1 border p-2 rounded" style="min-height: 50px;">
      <div v-if="this.pContent.content !== undefined">
        FAQ
      </div>
      <div v-else>
        {{ this.translate.loading_faq }}
      </div>
    </div>
  </div>
  <div v-if="this.pContent.type === 3">
    <div class="border-light border-1 border p-2 rounded" style="min-height: 50px;">
      <PreviewListing :p-content="this.pContent" v-if="this.pContent.content !== undefined"></PreviewListing>
      <div v-else>
        {{ this.translate.loading_listing }}
      </div>
    </div>

  </div>
</template>
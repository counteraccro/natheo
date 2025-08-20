<script>
import {PageRender} from "../../../../../../utils/Front/Const/PageRender";
import {UtilsFront} from "../../../../../../utils/Front/UtilsFront";
import {ContentType} from "../../../../../../utils/Front/Const/ContentType";
import ContentText from "./ContentText.vue";
import ContentFaq from "./ContentFaq.vue";
import ContentListing from "./ContentListing.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Content page
 */
export default {
  name: 'ContentStructure',
  computed: {
    ContentText() {
      return ContentText
    },
    PageRender() {
      return PageRender
    }
  },
  props: {
    data: Object,
    ajaxRequest: Object,
    locale: String,
    utilsFront: Object

  },
  emits: ['api-failure'],
  data() {
    return {
      page: this.data,
    }
  },
  created() {
  },

  mounted() {

  },

  methods: {

    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },

    getComponentByType(type) {
      if (type === ContentType.text) {
        return ContentText;
      }

      if (type === ContentType.faq) {
        return ContentFaq;
      }

      if (type === ContentType.listing) {
        return ContentListing;
      }
    }
  }
}
</script>

<template>

  <div v-if="this.page.headerImg !== ''" class="relative w-full h-48 sm:h-56 md:h-72 lg:h-80">
    <img :src="this.page.headerImg" alt="Image d'article" class="w-full h-full object-cover rounded-2xl">
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent rounded-2xl"></div>
    <h1 class="absolute inset-0 flex items-center justify-center text-white
             text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-bold text-center px-4">
      {{ this.page.title }}
    </h1>
  </div>

  <h1 v-else
      class="text-slate-900 text-3xl font-semibold after:content-[''] after:block after:h-1 after:w-full after:mt-2 after:rounded-full after:bg-theme-4-750 mb-4">
    {{ this.page.title }}
  </h1>
  <div class="p-2 rounded-b-2xl bg-white">
    <div v-if="this.page.render === PageRender.oneBlock">
      One block
    </div>
    <div v-if="this.page.render === PageRender.twoBlock">
      Two block
    </div>
    <div v-if="this.page.render === PageRender.threeBlock">
      3 block
    </div>
    <div v-if="this.page.render === PageRender.twoBlockBottom">
      <div class="grid grid-cols-12">
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[0].type)"
                     :data="this.page.contents[0]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[1].type)"
                     :data="this.page.contents[1]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.threeBlockBottom">
      3 block bottom
    </div>
    <div v-if="this.page.render === PageRender.oneTwoBlock">
      <div class="grid grid-cols-12">
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[0].type)"
                     :data="this.page.contents[0]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1].type)"
                     :data="this.page.contents[1]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-3">
          <component :is="this.getComponentByType(this.page.contents[2].type)"
                     :data="this.page.contents[2]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.twoOneBlock">
      2 bloc au dessus, 1 en dessous
    </div>
    <div v-if="this.page.render === PageRender.twoTwoBlock">

      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 lg:col-span-6 order-1">
          <component :is="this.getComponentByType(this.page.contents[0].type)"
                     :data="this.page.contents[0]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1].type)"
                     :data="this.page.contents[1]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-3">
          <component :is="this.getComponentByType(this.page.contents[2].type)"
                     :data="this.page.contents[2]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-4">
          <component :is="this.getComponentByType(this.page.contents[3].type)"
                     :data="this.page.contents[3]"
                     :utils-front="utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
  </div>
</template>
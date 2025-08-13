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
      tmp: [],
      nbElement: 0,
      load: {
        content: false
      }
    }
  },
  created() {
    this.nbElement = this.page.contents.length;
    this.loadContent();
  },

  mounted() {

  },

  methods: {

    loadContent() {
      let success = (datas) => {
        this.tmp.push(datas);
        this.page.contents.forEach((element, key) => {
          if (datas.id === element.id) {
            this.page.contents[key]['content'] = datas.content;
          }
        });

        if (this.tmp.length === this.nbElement) {
          this.load.content = true;
          this.tmp = [];
        }
      }

      let loader = () => {

      }
      this.page.contents.forEach((element) => {
        let params = {
          'id': element.id,
          'locale': this.locale
        };
        this.ajaxRequest.getContentPage(params, success, this.apiFailure, loader)
      });
    },

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
  <div v-if="this.load.content">
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
      two block bottom
    </div>
    <div v-if="this.page.render === PageRender.threeBlockBottom">
      3 block bottom
    </div>
    <div v-if="this.page.render === PageRender.oneTwoBlock">
      <div class="grid grid-cols-12">
        <div class="col-span-12 lg:order-1">
          <component :is="this.getComponentByType(this.page.contents[0].type)"
                     :data="this.page.contents[0].content"
                     :utils-front="utilsFront">
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1].type)"
                     :data="this.page.contents[1].content"
                     :utils-front="utilsFront">
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-3">
          <component :is="this.getComponentByType(this.page.contents[2].type)"
                     :data="this.page.contents[2].content"
                     :utils-front="utilsFront">
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.twoOneBlock">
      2 bloc au dessus, 1 en dessous
    </div>
    <div v-if="this.page.render === PageRender.twoTwoBlock">
      2 bloc au dessus, 2 block en dessous
    </div>

    <div class="grid grid-cols-12">

    </div>
  </div>
  <div v-else>
      <div class="mx-auto w-full rounded-md p-4">
        <div class="flex animate-pulse space-x-4">
          <div class="size-10 rounded-full bg-gray-200"></div>
          <div class="flex-1 space-y-6 py-1">
            <div class="h-2 rounded bg-gray-200"></div>
            <div class="space-y-3">
              <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2 h-2 rounded bg-gray-200"></div>
                <div class="col-span-1 h-2 rounded bg-gray-200"></div>
              </div>
              <div class="h-2 rounded bg-gray-200"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="mt-6"></div>
              <div class="h-4 bg-gray-200 rounded w-100 mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2 w-100"></div>

              <div class="mt-6"></div>
              <div class="h-4 bg-gray-200 rounded w-100 mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2 w-100"></div>
            </div>
          </div>
        </div>
      </div>
  </div>
</template>
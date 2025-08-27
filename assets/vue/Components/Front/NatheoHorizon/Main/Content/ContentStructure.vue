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
    translate: Object,
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

  <div v-if="this.page.headerImg !== null" class="relative w-full h-48 sm:h-56 md:h-72 lg:h-80">
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
  <div class="p-4 rounded-b-2xl bg-white">
    <div v-if="this.page.render === PageRender.oneBlock">

      <div class="grid grid-cols-12">
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>


    </div>
    <div v-if="this.page.render === PageRender.twoBlock">
      <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12 lg:col-span-6 order-1">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.threeBlock">

      <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12 lg:col-span-4 order-1">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-4 order-2">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-4 order-3">
          <component :is="this.getComponentByType(this.page.contents[2]?.type)"
                     :data="this.page.contents[2]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>

    </div>
    <div v-if="this.page.render === PageRender.twoBlockBottom">
      <div class="grid grid-cols-12">
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:order-2 mb-3">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.threeBlockBottom">
      <div class="grid grid-cols-12">
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:order-2 mb-3">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:order-3 mb-3">
          <component :is="this.getComponentByType(this.page.contents[2]?.type)"
                     :data="this.page.contents[2]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.oneTwoBlock">
      <div class="grid gap-5 grid-cols-12">
        <div class="col-span-12 lg:order-1 mb-3">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-3">
          <component :is="this.getComponentByType(this.page.contents[2]?.type)"
                     :data="this.page.contents[2]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>
    <div v-if="this.page.render === PageRender.twoOneBlock">

      <div class="grid gap-5 grid-cols-12">

        <div class="col-span-12 lg:col-span-6 order-1">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:order-3 mb-3">
          <component :is="this.getComponentByType(this.page.contents[2]?.type)"
                     :data="this.page.contents[2]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>

    </div>
    <div v-if="this.page.render === PageRender.twoTwoBlock">

      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 lg:col-span-6 order-1">
          <component :is="this.getComponentByType(this.page.contents[0]?.type)"
                     :data="this.page.contents[0]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-2">
          <component :is="this.getComponentByType(this.page.contents[1]?.type)"
                     :data="this.page.contents[1]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-3">
          <component :is="this.getComponentByType(this.page.contents[2]?.type)"
                     :data="this.page.contents[2]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
        <div class="col-span-12 lg:col-span-6 order-4">
          <component :is="this.getComponentByType(this.page.contents[3]?.type)"
                     :data="this.page.contents[3]"
                     :utils-front="this.utilsFront"
                     :locale="this.locale"
                     :ajax-request="this.ajaxRequest"
                     @api-failure="this.apiFailure"
          >
          </component>
        </div>
      </div>
    </div>

    <div class=" m-3 mt-12 border-t border-neutral-200/70 dark:border-neutral-800/70 pt-8">
      <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 sm:items-start">

        <div>
          <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
            <div class="shrink-0">
              <img v-if="this.page.author.avatar !== null || this.page.author.avatar !== ''"
                :src="'/uploads/avatars/' + this.page.author.avatar"
                :alt="'Avatar de ' + this.page.author.author"
                class="size-14 rounded-full object-cover"
              />

              <div v-else class="flex size-14 items-center justify-center rounded-full bg-theme-4-750 dark:bg-gray-600
                   text-white font-semibold ring-1 ring-neutral-200/70dark:ring-neutral-800/70"> {{ this.page.author.author.substring(0, 1).toUpperCase() }}
              </div>
            </div>

            <h3 class="mt-2 sm:mt-0 text-base font-semibold text-slate-900">
              {{ this.page.author.author }}
            </h3>
          </div>

          <p class="mt-2 ml-3 text-sm text-slate-600">
            {{ this.page.author.description }}
          </p>
        </div>

        <div class="sm:text-right">
          <time datetime="ISO_DATE" class="block text-sm text-slate-600">
            {{ this.translate.published }} {{ this.utilsFront.formatDate(this.page.created * 1000) }}
          </time>
          <time datetime="ISO_DATE" class="block text-sm text-slate-600">
            {{ this.translate.edit }} {{ this.utilsFront.formatDate(this.page.update * 1000) }}
          </time>
          <p class="mt-2 text-sm text-slate-600">
            {{ this.page.statistiques.PAGE_NB_READ }} {{ this.translate.statPublication }}
          </p>
        </div>
      </div>

      <div class="mt-6">
        <ul class="flex flex-wrap gap-2 justify-end">
          <li v-for="tag in this.page.tags">
            <a href="#"  class="inline-flex items-center rounded-full border border-neutral-200/70 px-3 py-1 text-sm text-neutral-700 hover:bg-theme-4-750 hover:!text-theme-1-100 hover:cursor-pointer hover:dark:bg-gray-600">
              {{ tag.label }}
            </a>
          </li>
        </ul>
      </div>
    </div>

  </div>
</template>
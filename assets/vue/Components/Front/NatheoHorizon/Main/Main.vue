<script>


import VerticalMenu from "./VerticalMenu.vue";
import ContentStructure from "./Content/ContentStructure.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Main',
  components: {ContentStructure, VerticalMenu},
  props: {
    utilsFront: Object,
    page: Object,
    ajaxRequest: Object,
    translate: Object,
    locale: String
  },
  emits: ['api-failure', 'api-loader'],
  data() {
    return {
    }
  },

  mounted() {},

  methods: {

    apiFailure(code, msg) {
      this.$emit('api-failure', code, msg);
    },

    apiSuccess(data) {
      console.log(data)
    },

    apiLoader(close) {
      this.$emit('api-loader', close);
    }

  }
}
</script>

<template>
  <div v-if="this.page.menus.LEFT && this.page.menus.RIGHT" class="grid grid-cols-12 gap-2 mt-2">
    <div class="col-span-12 lg:col-span-2 lg:order-1 rounded-2xl mr-1">
      <VerticalMenu
          :utils-front="this.utilsFront"
          :type="this.page.menus.LEFT.type"
          :slug="this.page.slug"
          :menu="this.page.menus.LEFT"
      />


    </div>
    <div class="col-span-12 lg:col-span-8 lg:order-2 order-3">
      <content-structure
          :data="this.page"
          :ajax-request="this.ajaxRequest"
          :translate="this.translate.articleFooter"
          :locale="this.locale"
          :utils-front="this.utilsFront"
          @api-failure="this.apiFailure"
      />
    </div>
    <div class="col-span-12 lg:col-span-2 lg:order-3 order-2 rounded-2xl ml-1">
      <VerticalMenu
          :utils-front="this.utilsFront"
          :type="this.page.menus.RIGHT.type"
          :slug="this.page.slug"
          :menu="this.page.menus.RIGHT"
      />
    </div>
  </div>

  <div v-else-if="this.page.menus.LEFT" class="grid grid-cols-12 gap-2 mt-2">
    <div class="col-span-12 lg:col-span-2 rounded-2xl mr-1">

      <VerticalMenu
          :utils-front="this.utilsFront"
          :type="this.page.menus.LEFT.type"
          :slug="this.page.slug"
          :menu="this.page.menus.LEFT"
      />
    </div>
    <div class="col-span-12 lg:col-span-10">
      <content-structure
          :data="this.page"
          :ajax-request="this.ajaxRequest"
          :translate="this.translate.articleFooter"
          :locale="this.locale"
          :utils-front="this.utilsFront"
          @api-failure="this.apiFailure"
      />
    </div>
  </div>

  <div v-else class="grid grid-cols-12 gap-2 mt-2">
    <div class="col-span-12 lg:col-span-10 lg:order-1 order-2">
      <content-structure
          :data="this.page"
          :ajax-request="this.ajaxRequest"
          :translate="this.translate.articleFooter"
          :locale="this.locale"
          :utils-front="this.utilsFront"
          @api-failure="this.apiFailure"
      />
    </div>
    <div class="col-span-12 lg:col-span-2 lg:order-2 order-1 rounded-2xl ml-1">
      <VerticalMenu
          :utils-front="this.utilsFront"
          :type="this.page.menus.RIGHT.type"
          :slug="this.page.slug"
          :menu="this.page.menus.RIGHT"
      />
    </div>
  </div>
</template>
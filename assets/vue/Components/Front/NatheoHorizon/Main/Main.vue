<script>


import VerticalMenu from "./VerticalMenu.vue";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Main',
  components: {VerticalMenu},
  props: {
    utilsFront: Object,
    page: Object,
    ajaxRequest: Object
  },
  emits: ['api-failure', 'api-loader'],
  data() {
    return {
      value: '',
    }
  },

  mounted() {
    //this.ajaxRequest.getPageBySlug(this.apiSuccess, this.apiFailure, this.apiLoader);
  },

  methods: {

    apiFailure(msg) {
      this.$emit('api-failure', msg);
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
    <div class="col-span-12 md:col-span-2 md:order-1 rounded bg-gray-300">Menu Main</div>
    <div class="col-span-12 md:col-span-8 md:order-2 order-3 rounded bg-gray-300">Content</div>
    <div class="col-span-12 md:col-span-2 md:order-3 order-2 rounded bg-gray-300">Menu 2</div>
  </div>

  <div v-else-if="this.page.menus.LEFT" class="grid grid-cols-12 gap-2 mt-2">
    <div class="col-span-12 md:col-span-2">
      <VerticalMenu
          :utils-front="this.utilsFront"
          :slug="this.page.slug"
          :menu="this.page.menus.LEFT"
      />
    </div>
    <div class="col-span-12 md:col-span-10 rounded bg-gray-300">Content</div>
  </div>

  <div v-else class="grid grid-cols-12 gap-2 mt-2">
    <div class="col-span-12 md:col-span-10 md:order-1 order-2 rounded bg-gray-300">Content</div>
    <div class="col-span-12 md:col-span-2 md:order-2 order-1 rounded bg-gray-300">
      Menu
    </div>
  </div>
</template>
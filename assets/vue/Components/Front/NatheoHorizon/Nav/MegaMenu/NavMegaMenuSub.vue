<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sous structure megaMenu
 */
export default {
  name: 'NavMegaMenuSub',
  props: {
    data: Object,
    utilsFront: Object,
    deep: Number
  },
  emits: [],
  data() {
    return {}
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>
  <ul :class="this.deep > 0 ? 'px-' + (this.deep+2):''">
    <li v-for="element in this.data">
      <a
          :href="this.utilsFront.getUrl(element)"
          class="!text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 px-2 py-1 rounded-md text-sm font-medium"
          :target="element.target">
        {{ element.label }}
      </a>
      <nav-mega-menu-sub v-if="element.hasOwnProperty('elements')"
                         :data="element.elements"
                         :utils-front="this.utilsFront"
                         :deep="this.deep+1"
      />
    </li>
  </ul>
</template>
<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Element du vertical menu
 */
export default {
  name: 'VerticalMenuElement',
  props: {
    utilsFront: Object,
    slug: String,
    element: Object,
    deep: Number,
    size: Number,
    index: Number
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },

  methods: {

    getClassSummary() {
      let css = 'px-6 py-2 text-gray-700 hover:bg-gray-100 font-medium';
      if(this.deep === 0) {
        css = 'px-5 py-3 font-semibold text-gray-800 hover:bg-gray-500';
      }

      if(this.size === 1) {
        css += ' rounded-xl';
      }

      if(this.size === this.index+1) {
        css += ' rounded-b-xl';
      }

      return css;
    }

  }
}
</script>

<template>

  <details :class="this.deep === 0 && (this.size > 1 && (this.index + 1) !== this.size) ? 'border-b group/group-' + this.deep : 'group/group-' + this.deep">
    <summary
        class="flex items-center justify-between cursor-pointer"
    :class="this.getClassSummary()">
      {{ element.label }}
      <span class="transition-transform text-gray-500" :class="'group-open/group-' + this.deep + ':rotate-90'">➤</span>
    </summary>
    <ul class="bg-gray-50 space-y-1 pb-2" :class="this.deep === 0 && (this.size > 1 && (this.index + 1) !== this.size) ? 'border-b' : ''">
      <li v-for="(sub, index) in element.elements">
        <a v-if="!sub.elements" :href="this.utilsFront.getUrl(sub)" :target="element.target"
           class="block px-6 py-2 text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition">
          {{ sub.label }}
        </a>
        <vertical-menu-element v-else
                               :utils-front="this.utilsFront"
                               :slug="this.slug"
                               :element="sub"
                               :deep="this.deep+1"
                               :index="index"
        />
      </li>
    </ul>
  </details>

</template>
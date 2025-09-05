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
      let css = 'px-6 py-2 text-slate-600 hover:bg-theme-4-750 hover:!text-theme-1-100 font-medium';
      if(this.deep === 0) {
        css = 'px-5 py-3 font-semibold text-slate-800 hover:bg-theme-4-750 hover:!text-theme-1-100';
      }

      if(this.size === 1) {
        css += ' rounded-xl';
      }

      if(this.size === this.index+1) {
        css += ' rounded-b-xl';
      }

      return css += ' group/group-' + this.deep;
    }

  }
}
</script>

<template>

  <details :class="this.deep === 0 && (this.size > 1 && (this.index + 1) !== this.size) ? 'border-b border-neutral-200/70 group/group-' + this.deep : 'group/group-' + this.deep">
    <summary
        class="flex items-center justify-between cursor-pointer"
    :class="this.getClassSummary()">
      {{ element.label }}
      <span class="transition-transform text-gray-500" :class="'group-open/group-' + this.deep + ':rotate-90 group-hover/group-' + this.deep + ':!text-theme-1-100'">➤</span>
    </summary>
    <ul class="bg-gray-50 space-y-1" :class="this.deep === 0 && (this.size > 1 && (this.index + 1) !== this.size) ? 'border-b border-neutral-200/70' : ''">
      <li v-for="(sub, index) in element.elements">
        <a v-if="!sub.elements" :href="this.utilsFront.getUrl(sub)" :target="element.target"
           class="block px-6 py-2 text-gray-600 hover:bg-theme-4-750 hover:!text-theme-1-100 transition">
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
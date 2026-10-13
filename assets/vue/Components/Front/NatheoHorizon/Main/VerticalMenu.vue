<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Menu vertical (gauche ou droite)
 */
import { defineComponent, type PropType } from 'vue';
import VerticalMenuElement from './VerticalMenuElement.vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenu } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'VerticalMenu',
  components: { VerticalMenuElement },
  props: {
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    menu: {
      type: Object as PropType<FrontMenu>,
      required: true,
    },
  },
  methods: {
    /**
     * Arrondi au survol du premier / dernier élément
     */
    getHoverClass(index: number): string {
      if (index === 0) {
        return 'hover:rounded-t-xl';
      }
      return index + 1 === this.menu.elements.length ? 'hover:rounded-b-xl' : '';
    },
  },
});
</script>

<template>
  <nav class="w-full bg-white shadow-md rounded-xl border border-neutral-200/70 sticky top-5">
    <div v-for="(element, index) in menu.elements" :key="element.id">
      <a
        v-if="!element.elements"
        :href="utilsFront.getUrl(element)"
        :target="element.target"
        class="block px-5 py-3 font-semibold text-gray-800 hover:bg-theme-4-750 hover:text-theme-1-100! transition border-b border-neutral-200/70"
        :class="getHoverClass(index)"
      >
        {{ element.label }}
      </a>
      <VerticalMenuElement
        v-else
        :utils-front="utilsFront"
        :element="element"
        :deep="0"
        :is-last="index + 1 === menu.elements.length"
        :is-alone="menu.elements.length === 1"
      />
    </div>
  </nav>
</template>

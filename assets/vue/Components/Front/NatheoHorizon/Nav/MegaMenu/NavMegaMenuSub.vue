<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Sous structure (récursive) du mega menu
 */
import { defineComponent, type PropType } from 'vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'NavMegaMenuSub',
  props: {
    elements: {
      type: Array as PropType<FrontMenuElement[]>,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    deep: {
      type: Number,
      required: true,
    },
  },
});
</script>

<template>
  <ul :style="deep > 0 ? { paddingInline: `${(deep + 2) * 0.25}rem` } : undefined">
    <li v-for="element in elements" :key="element.id">
      <a
        :href="utilsFront.getUrl(element)"
        class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-2 py-1 rounded-md font-medium hover:dark:bg-gray-600"
        :target="element.target"
      >
        {{ element.label }}
      </a>
      <NavMegaMenuSub v-if="element.elements" :elements="element.elements" :utils-front="utilsFront" :deep="deep + 1" />
    </li>
  </ul>
</template>

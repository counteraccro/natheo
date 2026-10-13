<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Composant (récursif) pour l'affichage du footer en colonne
 */
import { defineComponent, type PropType } from 'vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'FooterColonne',
  props: {
    elements: {
      type: Array as PropType<FrontMenuElement[]>,
      required: true,
    },
    deep: {
      type: Number,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
  },
});
</script>

<template>
  <ul class="space-y-2" :class="deep > 0 ? 'list-inside pl-3' : 'mt-6'">
    <li v-for="element in elements" :key="element.id">
      <a
        :href="utilsFront.getUrl(element)"
        :target="element.target"
        class="text-slate-600 font-normal hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600 p-1.5"
      >
        {{ element.label }}
      </a>
      <FooterColonne v-if="element.elements" :elements="element.elements" :deep="deep + 1" :utils-front="utilsFront" />
    </li>
  </ul>
</template>

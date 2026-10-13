<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Sous-menu (récursif) du menu déroulant desktop
 */
import { defineComponent, type PropType } from 'vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'NavMenuDropdownSub',
  props: {
    elements: {
      type: Array as PropType<FrontMenuElement[]>,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    level: {
      type: Number,
      required: true,
    },
  },
});
</script>

<template>
  <li
    v-for="element in elements"
    :key="element.id"
    class="relative focus-within:bg-theme-4-750 focus-within:rounded-md focus-within:text-theme-1-100"
  >
    <a
      v-if="!element.elements"
      :href="utilsFront.getUrl(element)"
      :target="element.target"
      class="block px-4 py-2 hover:bg-theme-4-750 text-gray-500! hover:text-theme-1-100! rounded-md hover:dark:bg-gray-600"
      >{{ element.label }}</a
    >
    <template v-else>
      <button
        type="button"
        aria-haspopup="true"
        class="w-full flex items-center justify-between px-4 py-2 text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! rounded-md cursor-pointer hover:dark:bg-gray-600"
      >
        {{ element.label }}
        <svg class="size-5 inline-block -rotate-90 ml-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path
            fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </button>
      <div
        class="nav-submenu absolute left-full top-0 min-w-max bg-white border border-gray-200 rounded-lg shadow-lg"
        :style="{ zIndex: 20 + level * 10 }"
      >
        <ul class="py-2 px-4">
          <NavMenuDropdownSub :elements="element.elements" :utils-front="utilsFront" :level="level + 1" />
        </ul>
      </div>
    </template>
  </li>
</template>

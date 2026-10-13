<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Menu déroulant (récursif) pour la version mobile
 */
import { defineComponent, type PropType } from 'vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'NavMenuDropdownMobile',
  props: {
    elements: {
      type: Array as PropType<FrontMenuElement[]>,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
  },
  data() {
    return {
      openIds: [] as number[],
    };
  },
  methods: {
    toggle(id: number): void {
      const index = this.openIds.indexOf(id);
      if (index === -1) {
        this.openIds.push(id);
      } else {
        this.openIds.splice(index, 1);
      }
    },
  },
});
</script>

<template>
  <li v-for="element in elements" :key="element.id">
    <a
      v-if="!element.elements"
      :href="utilsFront.getUrl(element)"
      class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! block px-3 py-2 rounded-md text-base font-medium hover:dark:bg-gray-600"
      :target="element.target"
    >
      {{ element.label }}
    </a>

    <template v-else>
      <button
        type="button"
        class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-3 py-2 rounded-md text-base font-medium w-full flex items-center justify-between cursor-pointer hover:dark:bg-gray-600"
        :aria-expanded="openIds.includes(element.id)"
        @click="toggle(element.id)"
      >
        {{ element.label }}
        <svg
          class="h-5 w-5 transition-transform"
          :class="{ 'rotate-180': openIds.includes(element.id) }"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path
            fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </button>
      <ul v-show="openIds.includes(element.id)" class="ps-4 py-2">
        <NavMenuDropdownMobile :elements="element.elements" :utils-front="utilsFront" />
      </ul>
    </template>
  </li>
</template>

<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Menu déroulant (desktop)
 */
import { defineComponent, type PropType } from 'vue';
import NavMenuDropdownSub from './NavMenuDropdownSub.vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'NavMenuDropdown',
  components: { NavMenuDropdownSub },
  props: {
    data: {
      type: Object as PropType<FrontMenuElement>,
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
  <div class="relative group inline-block">
    <button
      type="button"
      aria-haspopup="true"
      class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-4 py-2 rounded-md font-medium cursor-pointer hover:dark:bg-gray-600"
    >
      {{ data.label }}
      <svg class="size-5 inline-block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path
          fill-rule="evenodd"
          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <div
      class="absolute z-10 hidden group-hover:block group-focus-within:block min-w-max bg-white border border-gray-200 rounded-lg shadow-lg mt-1 opacity-0 translate-y-2 transition-all duration-200 ease-out group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:translate-y-0"
    >
      <ul class="py-2 px-4">
        <NavMenuDropdownSub :elements="data.elements ?? []" :utils-front="utilsFront" :level="1" />
      </ul>
    </div>
  </div>
</template>

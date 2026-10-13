<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Mega menu
 */
import { defineComponent, type PropType } from 'vue';
import NavMegaMenuSub from './NavMegaMenuSub.vue';
import { MenuType } from '@/utils/Front/Const/Menu';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

const FIXED_COLUMNS: Record<number, number> = {
  [MenuType.headerBigMenu2column]: 2,
  [MenuType.headerBigMenu3column]: 3,
  [MenuType.headerBigMenu4column]: 4,
};

export default defineComponent({
  name: 'NavMegaMenu',
  components: { NavMegaMenuSub },
  props: {
    data: {
      type: Object as PropType<FrontMenuElement>,
      required: true,
    },
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    type: {
      type: Number,
      required: true,
    },
  },
  computed: {
    elementsWithChildren(): FrontMenuElement[] {
      return (this.data.elements ?? []).filter((element) => element.elements);
    },

    elementsWithoutChildren(): FrontMenuElement[] {
      return (this.data.elements ?? []).filter((element) => !element.elements);
    },

    /**
     * Nombre de colonnes : fixé par le type de menu, sinon une par sous-menu plus une pour les liens simples
     */
    nbColumns(): number {
      return (
        FIXED_COLUMNS[this.type] ?? this.elementsWithChildren.length + (this.elementsWithoutChildren.length > 0 ? 1 : 0)
      );
    },
  },
});
</script>

<template>
  <div class="relative group inline-block">
    <button
      type="button"
      aria-haspopup="true"
      class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-4 py-2 rounded-md font-medium inline-flex items-center gap-1 cursor-pointer hover:dark:bg-gray-600"
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
      class="absolute left-0 hidden group-hover:block group-focus-within:block bg-white border border-gray-200 rounded-lg shadow-lg z-20 w-[700px] opacity-0 translate-y-3 transition-all duration-300 ease-out group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:translate-y-0"
    >
      <div class="grid gap-6 p-6" :style="{ gridTemplateColumns: `repeat(${nbColumns}, minmax(0, 1fr))` }">
        <div v-for="element in elementsWithChildren" :key="element.id">
          <h3 class="font-semibold text-gray-700 mb-2">
            {{ element.label }}
          </h3>
          <NavMegaMenuSub :elements="element.elements ?? []" :utils-front="utilsFront" :deep="0" />
        </div>

        <div v-if="elementsWithoutChildren.length > 0">
          <ul>
            <li v-for="element in elementsWithoutChildren" :key="element.id">
              <a
                :href="utilsFront.getUrl(element)"
                class="text-gray-500! hover:bg-theme-4-750 hover:text-theme-1-100! px-3 py-2 rounded-md font-medium hover:dark:bg-gray-600"
                :target="element.target"
              >
                {{ element.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

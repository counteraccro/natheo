<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Élément (récursif) du menu vertical, sous forme d'accordéon
 */
import { defineComponent, type PropType } from 'vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontMenuElement } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'VerticalMenuElement',
  props: {
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    element: {
      type: Object as PropType<FrontMenuElement>,
      required: true,
    },
    deep: {
      type: Number,
      required: true,
    },
    isLast: {
      type: Boolean,
      default: false,
    },
    isAlone: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      open: false,
    };
  },
  computed: {
    hasBorder(): boolean {
      return this.deep === 0 && !this.isLast;
    },

    summaryClass(): string[] {
      const css =
        this.deep === 0
          ? ['px-5', 'py-3', 'font-semibold', 'text-slate-800']
          : ['px-6', 'py-2', 'font-medium', 'text-slate-600'];

      // L'arrondi du dernier élément n'est conservé que lorsqu'il est replié
      if (this.isAlone) {
        css.push(this.open ? 'rounded-t-xl' : 'rounded-xl');
      } else if (this.isLast && !this.open) {
        css.push('rounded-b-xl');
      }
      return css;
    },
  },
  methods: {
    onToggle(event: Event): void {
      this.open = (event.target as HTMLDetailsElement).open;
    },
  },
});
</script>

<template>
  <details :class="{ 'border-b border-neutral-200/70': hasBorder }" @toggle="onToggle">
    <summary
      class="group flex items-center justify-between cursor-pointer hover:bg-theme-4-750 hover:text-theme-1-100! [&::-webkit-details-marker]:hidden"
      :class="summaryClass"
    >
      {{ element.label }}
      <span
        class="transition-transform text-gray-500 group-hover:text-theme-1-100!"
        :class="{ 'rotate-90': open }"
        aria-hidden="true"
        >➤</span
      >
    </summary>
    <ul class="bg-gray-50 space-y-1" :class="{ 'border-b border-neutral-200/70': hasBorder }">
      <li v-for="(sub, index) in element.elements" :key="sub.id">
        <a
          v-if="!sub.elements"
          :href="utilsFront.getUrl(sub)"
          :target="sub.target"
          class="block px-6 py-2 text-gray-600 hover:bg-theme-4-750 hover:text-theme-1-100! transition"
        >
          {{ sub.label }}
        </a>
        <VerticalMenuElement
          v-else
          :utils-front="utilsFront"
          :element="sub"
          :deep="deep + 1"
          :is-last="index + 1 === element.elements?.length"
        />
      </li>
    </ul>
  </details>
</template>

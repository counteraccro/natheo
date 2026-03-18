<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Affichage de la navBar des médias
 */
import { defineComponent, PropType } from 'vue';

type BreadcrumbItem = {
  id: number;
  name: string;
};

type Paths = {
  root: BreadcrumbItem[];
};

export default defineComponent({
  name: 'MediasBreadcrum',
  props: {
    paths: {
      type: Object as PropType<Paths>,
      default: () => ({}),
    },
  },
  emits: ['load-folder'],
});
</script>

<style></style>

<template>
  <div class="flex items-center gap-0 text-sm">
    <button
      href="#"
      class="flex items-center gap-1.5 px-2 py-1 rounded-md font-medium transition text-[var(--primary)] bg-[var(--primary-lighter)] cursor-pointer"
      @click="$emit('load-folder', 0)"
    >
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
        <path
          d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
        ></path>
      </svg>
      Root
    </button>
    <svg
      v-if="Object.keys(paths ?? {}).length !== 0"
      class="w-4 h-4"
      style="color: var(--text-light)"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
    <div v-for="item in paths" class="flex items-center gap-1">
      <button
        @click="paths.indexOf(item) !== Object.keys(paths).length - 1 ? $emit('load-folder', item.id) : ''"
        class="flex items-center gap-1.5 px-2 py-1 rounded-md font-medium transition hover:text-[var(--primary)]"
        :class="
          paths.indexOf(item) !== Object.keys(paths).length - 1
            ? 'hover:bg-[var(--primary-lighter)] text-[var(--text-secondary)] cursor-pointer'
            : ''
        "
        style="color: var(--text-secondary)"
      >
        <svg style="color: var(--primary)" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
          <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.11-.9-2-2-2h-8l-2-2z"></path>
        </svg>
        {{ item.name }}
      </button>
      <svg
        v-if="paths.indexOf(item) !== Object.keys(paths).length - 1"
        class="w-4 h-4"
        style="color: var(--text-light)"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </div>
  </div>
</template>

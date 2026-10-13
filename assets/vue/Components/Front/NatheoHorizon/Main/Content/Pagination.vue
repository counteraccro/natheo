<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pagination partagée entre les listings et les commentaires
 */
import { defineComponent, type PropType } from 'vue';
import type { FrontPaginationTranslate } from '@/ts/Front/NatheoHorizon/Structure.type';
import type { PaginationItem } from '@/ts/Front/NatheoHorizon/Pagination.type';

const BASE_CLASS = 'px-3 py-1 rounded-lg border transition';
const ACTIVE_CLASS = 'border-gray-300 bg-theme-4-750 text-theme-1-100! hover:dark:bg-gray-600';
const DEFAULT_CLASS =
  'border-gray-300 text-gray-600 cursor-pointer hover:bg-theme-4-750 hover:text-theme-1-100! hover:dark:bg-gray-600';
const DISABLED_CLASS = 'border-gray-100 text-gray-300 cursor-not-allowed';

export default defineComponent({
  name: 'Pagination',
  props: {
    page: {
      type: Number,
      required: true,
    },
    nbPages: {
      type: Number,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontPaginationTranslate>,
      required: true,
    },
  },
  emits: {
    change: (_page: number) => true,
  },
  computed: {
    /**
     * Pages affichées : les 2 premières, les 2 dernières, la courante et ses voisines, une ellipse au-delà
     */
    items(): PaginationItem[] {
      const items: PaginationItem[] = [];
      for (let n = 1; n <= this.nbPages; n++) {
        if (Math.abs(n - this.page) <= 1 || n <= 2 || n >= this.nbPages - 1) {
          items.push({ type: 'page', page: n });
        } else if (Math.abs(n - this.page) === 2) {
          items.push({ type: 'ellipsis', key: 'ellipsis-' + n });
        }
      }
      return items;
    },

    isFirst(): boolean {
      return this.page <= 1;
    },

    isLast(): boolean {
      return this.page >= this.nbPages;
    },
  },
  methods: {
    goTo(page: number): void {
      if (page < 1 || page > this.nbPages || page === this.page) {
        return;
      }
      this.$emit('change', page);
    },

    navClass(disabled: boolean): string[] {
      return [BASE_CLASS, 'text-sm', disabled ? DISABLED_CLASS : DEFAULT_CLASS];
    },

    pageClass(page: number): string[] {
      return [BASE_CLASS, page === this.page ? ACTIVE_CLASS : DEFAULT_CLASS];
    },
  },
});
</script>

<template>
  <nav class="flex flex-wrap gap-y-3 items-center justify-center mt-8 space-x-1" :aria-label="translate.label">
    <button type="button" :class="navClass(isFirst)" :disabled="isFirst" :aria-label="translate.first" @click="goTo(1)">
      &laquo;
    </button>
    <button
      type="button"
      :class="navClass(isFirst)"
      :disabled="isFirst"
      :aria-label="translate.previous"
      @click="goTo(page - 1)"
    >
      &lsaquo;
    </button>

    <template v-for="item in items" :key="item.type === 'page' ? item.page : item.key">
      <button
        v-if="item.type === 'page'"
        type="button"
        :class="pageClass(item.page)"
        :aria-current="item.page === page ? 'page' : undefined"
        @click="goTo(item.page)"
      >
        {{ item.page }}
      </button>
      <span v-else class="px-2 text-gray-400" aria-hidden="true">…</span>
    </template>

    <button
      type="button"
      :class="navClass(isLast)"
      :disabled="isLast"
      :aria-label="translate.next"
      @click="goTo(page + 1)"
    >
      &rsaquo;
    </button>
    <button
      type="button"
      :class="navClass(isLast)"
      :disabled="isLast"
      :aria-label="translate.last"
      @click="goTo(nbPages)"
    >
      &raquo;
    </button>
  </nav>
</template>

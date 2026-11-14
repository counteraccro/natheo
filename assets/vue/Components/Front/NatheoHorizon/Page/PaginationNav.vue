<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Navigation entre les pages d'une collection : précédente / suivante, numéros sauf en mode compact
 */
import { computed } from 'vue';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import type { FrontPaginationTranslate } from '@/ts/Front/FrontTranslate.type';

const props = defineProps<{ page: number; lastPage: number; translate: FrontPaginationTranslate; compact?: boolean }>();
const emit = defineEmits<{ go: [page: number] }>();

/** Première, dernière et pages voisines ; null pour une coupure */
const numbers = computed(() => {
  const pages = new Set([1, props.lastPage, props.page - 1, props.page, props.page + 1]);
  const sorted = [...pages].filter((n) => n >= 1 && n <= props.lastPage).sort((a, b) => a - b);
  return sorted.flatMap((n, i) => (i > 0 && n - sorted[i - 1] > 1 ? [null, n] : [n]));
});

const button =
  'inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-nh px-4 font-semibold text-fg shadow-[inset_0_0_0_1.5px_currentColor] disabled:cursor-default disabled:opacity-45';
</script>

<template>
  <nav v-if="lastPage > 1" :aria-label="translate.label" class="mt-8 flex items-center justify-between gap-4">
    <button type="button" :class="button" :disabled="page <= 1" @click="emit('go', page - 1)">
      <HorizonIcon name="chevron-down" class="size-4 rotate-90" />
      <span class="max-sm:sr-only">{{ translate.previous }}</span>
    </button>
    <ol v-if="!compact" class="flex gap-1.5">
      <li v-for="(number, index) in numbers" :key="index">
        <span v-if="number === null" aria-hidden="true" class="grid h-11 min-w-11 place-items-center">…</span>
        <span
          v-else-if="number === page"
          aria-current="page"
          class="grid h-11 min-w-11 place-items-center rounded-nh bg-primary font-semibold text-on-primary"
        >
          {{ number }}
        </span>
        <button
          v-else
          type="button"
          class="grid h-11 min-w-11 cursor-pointer place-items-center rounded-nh font-semibold text-fg hover:bg-surface"
          @click="emit('go', number)"
        >
          {{ number }}
        </button>
      </li>
    </ol>
    <span v-else class="text-[0.95rem] text-fg-muted">{{ page }} {{ translate.of }} {{ lastPage }}</span>
    <button type="button" :class="button" :disabled="page >= lastPage" @click="emit('go', page + 1)">
      <span class="max-sm:sr-only">{{ translate.next }}</span>
      <HorizonIcon name="chevron-down" class="size-4 -rotate-90" />
    </button>
  </nav>
</template>

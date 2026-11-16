<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Navigation entre les pages d'une collection
 */
import type { FrontPaginationTranslate } from '@/ts/Front/FrontTranslate.type';

defineProps<{ page: number; lastPage: number; translate: FrontPaginationTranslate }>();
const emit = defineEmits<{ go: [page: number] }>();
</script>

<template>
  <nav v-if="lastPage > 1" :aria-label="translate.label" class="flex items-center gap-2 text-sm">
    <button type="button" :disabled="page <= 1" :aria-label="translate.first" @click="emit('go', 1)">«</button>
    <button type="button" :disabled="page <= 1" :aria-label="translate.previous" @click="emit('go', page - 1)">
      ‹
    </button>
    <span>{{ page }} {{ translate.of }} {{ lastPage }}</span>
    <button type="button" :disabled="page >= lastPage" :aria-label="translate.next" @click="emit('go', page + 1)">
      ›
    </button>
    <button type="button" :disabled="page >= lastPage" :aria-label="translate.last" @click="emit('go', lastPage)">
      »
    </button>
  </nav>
</template>

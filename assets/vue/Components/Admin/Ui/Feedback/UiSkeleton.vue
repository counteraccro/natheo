<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Squelette de chargement : lignes de texte, bloc ou cellules d'une ligne de grille ; masqué aux lecteurs d'écran,
 * la zone parente porte aria-busy
 */
withDefaults(defineProps<{ variant?: 'text' | 'block' | 'row'; lines?: number; columns?: number }>(), {
  variant: 'text',
  lines: 3,
  columns: 4,
});

const WIDTHS = ['w-full', 'w-11/12', 'w-4/5', 'w-2/3', 'w-3/5'];
</script>

<template>
  <div v-if="variant === 'text'" class="flex flex-col gap-2.5" aria-hidden="true">
    <span v-for="n in lines" :key="n" class="ui-skeleton block h-3 rounded-md" :class="WIDTHS[(n - 1) % WIDTHS.length]" />
  </div>
  <span v-else-if="variant === 'block'" class="ui-skeleton block h-24 w-full rounded-lg" aria-hidden="true" />
  <div v-else class="flex items-center gap-4" aria-hidden="true">
    <span v-for="n in columns" :key="n" class="ui-skeleton block h-3 flex-1 rounded-md" :class="n === 1 && 'max-w-40'" />
  </div>
</template>

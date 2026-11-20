<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Titre d'un groupe d'éléments dans un UiDropdown, précédé d'un séparateur sauf en tête
 */
defineProps<{ separator?: boolean }>();
</script>

<template>
  <hr v-if="separator" class="my-1.5 border-0 border-t border-line" />
  <div role="presentation" class="px-2.5 pt-2 pb-1.5 text-xs text-fg-subtle"><slot /></div>
</template>

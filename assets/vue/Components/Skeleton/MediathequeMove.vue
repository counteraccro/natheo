<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonMediathequeMove',
  props: {},
  emits: [],
  data() {
    return {};
  },
  mounted() {},

  methods: {},
};
</script>

<template>
  <div class="p-3">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
        <div class="w-24 h-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
      <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
    </div>

    <!-- Search mini -->
    <div class="w-full h-7 rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>

    <!-- Arborescence -->
    <div class="mb-3 space-y-1">
      <!-- Racine -->
      <div class="flex items-center gap-2 px-1 py-1">
        <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse flex-shrink-0"></div>
        <div class="w-16 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
      <!-- Sous-dossiers -->
      <div class="flex items-center gap-2 px-1 py-1 ml-4">
        <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse flex-shrink-0"></div>
        <div class="w-20 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
      <div class="flex items-center gap-2 px-1 py-1 ml-4">
        <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse flex-shrink-0"></div>
        <div class="w-28 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
      <div class="flex items-center gap-2 px-1 py-1 ml-4">
        <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse flex-shrink-0"></div>
        <div class="w-16 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
    </div>

    <!-- Dossier sélectionné -->
    <div class="w-full h-8 rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse mb-3"></div>

    <!-- Boutons -->
    <div class="flex gap-2">
      <div class="flex-1 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
    </div>
  </div>
</template>

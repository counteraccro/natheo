<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonForm',
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
  <div class="space-y-6">
    <!-- Input 1 -->
    <div>
      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-2 animate-pulse"></div>
      <div class="h-11 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
      <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-48 mt-1 animate-pulse"></div>
    </div>

    <!-- Input 2 -->
    <div>
      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 mb-2 animate-pulse"></div>
      <div class="h-11 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
      <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-56 mt-1 animate-pulse"></div>
    </div>

    <!-- Textarea -->
    <div>
      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-28 mb-2 animate-pulse"></div>
      <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
      <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-44 mt-1 animate-pulse"></div>
    </div>

    <!-- Couleur -->
    <div>
      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2 animate-pulse"></div>
      <div class="flex items-center gap-3">
        <div class="h-10 w-20 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        <div class="flex-1 h-11 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
      </div>
      <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-40 mt-1 animate-pulse"></div>
    </div>

    <!-- Select -->
    <div>
      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 mb-2 animate-pulse"></div>
      <div class="h-11 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
    </div>

    <!-- Checkboxes -->
    <div class="space-y-3">
      <div class="flex items-center gap-3">
        <div class="h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-36 animate-pulse"></div>
      </div>
      <div class="flex items-center gap-3">
        <div class="h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-28 animate-pulse"></div>
      </div>
    </div>

    <!-- Boutons -->
    <div class="flex gap-3 pt-4">
      <div class="flex-1 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
      <div class="h-10 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
    </div>
  </div>
</template>

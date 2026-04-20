<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonRenderMenu',
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
  <div
    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mb-5 overflow-hidden"
  >
    <!-- Card header -->
    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="h-4 w-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
    </div>
    <!-- Card body -->
    <div class="p-4">
      <!-- Barre preview -->
      <div
        class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          <div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
        </div>
        <div class="flex items-center gap-6">
          <div class="h-4 w-16 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          <div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          <div class="h-4 w-12 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
        </div>
        <div class="w-8 h-8 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
      <!-- Warning -->
      <div class="flex items-center gap-2 mt-2">
        <div class="w-3.5 h-3.5 rounded-full flex-shrink-0 bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
        <div class="h-3 w-96 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      </div>
    </div>
  </div>
</template>

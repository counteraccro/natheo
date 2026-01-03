<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonCardStat',
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
  <div role="status" class="animate-pulse grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <!-- Stat Card 1 -->
    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-20 mb-3"></div>
          <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-12"></div>
        </div>
        <div class="w-12 h-12 bg-gray-200 rounded-full dark:bg-gray-700"></div>
      </div>
    </div>

    <!-- Stat Card 2 -->
    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-24 mb-3"></div>
          <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-12"></div>
        </div>
        <div class="w-12 h-12 bg-gray-200 rounded-full dark:bg-gray-700"></div>
      </div>
    </div>

    <!-- Stat Card 3 -->
    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-16 mb-3"></div>
          <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-16"></div>
        </div>
        <div class="w-12 h-12 bg-gray-200 rounded-full dark:bg-gray-700"></div>
      </div>
    </div>
  </div>
</template>

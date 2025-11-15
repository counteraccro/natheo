<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonSearchResult',
  props: {
    rows: {
      type: Number,
      default: 5,
    },
  },
  emits: [],
  data() {
    return {};
  },
  mounted() {},

  methods: {},
};
</script>

<template>
  <h2 class="text-xl font-bold animate-pulse bg-gray-200 h-6 w-1/3 mb-2 mt-2"></h2>
  <p class="text-sm animate-pulse bg-gray-200 h-4 w-1/4 mb-4"></p>
  <div v-for="row in rows" :key="row" class="bg-white rounded-lg flex mb-3 p-6 gap-2.5">
    <div>
      <div class="h-20 w-32 bg-gray-200 rounded animate-pulse"></div>
    </div>
    <div class="flex-1">
      <div class="h-6 w-3/4 bg-gray-200 rounded mb-2 animate-pulse"></div>
      <div class="h-4 w-1/2 bg-gray-200 rounded mb-2 animate-pulse"></div>
      <div class="h-4 w-3/4 bg-gray-200 rounded mb-2 animate-pulse"></div>
      <div class="flex gap-2.5 mt-4">
        <div class="h-4 w-1/4 bg-gray-200 rounded animate-pulse"></div>
        <div class="h-4 w-1/4 bg-gray-200 rounded animate-pulse"></div>
        <div class="h-4 w-1/4 bg-gray-200 rounded animate-pulse"></div>
      </div>
    </div>
    <div class="ml-auto flex gap-2">
      <button class="h-8 w-8 bg-gray-200 rounded-full animate-pulse"></button>
      <button class="h-8 w-8 bg-gray-200 rounded-full animate-pulse"></button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonPageTag',
});
</script>

<template>
  <div class="animate-pulse">
    <div class="card rounded-xl p-6">
      <div class="h-5 w-20 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="mb-6">
        <div class="h-3.5 w-48 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
        <div class="flex gap-3">
          <div class="h-10 flex-1 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-10 w-28 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
        </div>
        <div class="h-3 w-80 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
      </div>
      <div>
        <div class="h-3.5 w-36 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div>
        <div class="flex gap-2">
          <div class="h-7 w-20 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
          <div class="h-7 w-14 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
          <div class="h-7 w-24 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

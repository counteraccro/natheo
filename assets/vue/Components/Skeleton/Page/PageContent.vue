<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonPageContent',
});
</script>

<template>
  <div class="animate-pulse space-y-6">
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
      <div class="h-9 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
      <div class="h-9 w-14 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
      <div class="h-9 w-20 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
      <div class="h-9 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
      <div class="h-9 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
    </div>

    <div class="card rounded-lg p-6">
      <div class="h-5 w-44 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="space-y-5">
        <div>
          <div class="h-3.5 w-36 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-3 w-48 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div>
          <div class="h-3.5 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-3 w-56 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div>
          <div class="h-3.5 w-24 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-3 w-36 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div>
          <div class="h-3.5 w-52 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-3 w-44 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div>
          <div class="h-3.5 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-32 w-full bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
          <div class="h-3 w-52 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
      </div>
    </div>

    <div class="card rounded-xl p-6">
      <div class="h-5 w-56 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="h-44 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        <div class="h-44 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
      </div>
      <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
    </div>
  </div>
</template>

<style scoped></style>

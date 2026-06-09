<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonPageSave',
});
</script>

<template>
  <div class="animate-pulse space-y-6">
    <div class="card rounded-xl p-6">
      <div class="h-5 w-44 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-11 h-11 bg-gray-200 dark:bg-gray-700 rounded-xl flex-shrink-0"></div>
          <div class="space-y-2">
            <div class="h-3.5 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
            <div class="h-3 w-48 bg-gray-200 dark:bg-gray-700 rounded"></div>
          </div>
        </div>
        <div class="h-6 w-11 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
      </div>
    </div>
    <div class="card rounded-xl p-6">
      <div class="h-5 w-44 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="flex gap-3">
        <div class="h-10 w-48 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        <div class="h-10 w-52 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
      </div>
    </div>
    <div class="card rounded-xl p-6">
      <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
      <div class="h-3 w-72 bg-gray-200 dark:bg-gray-700 rounded mb-5"></div>
      <div class="h-9 w-56 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
    </div>
  </div>
</template>

<style scoped></style>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonPageSEO',
});
</script>

<template>
  <div class="animate-pulse space-y-6">
    <div class="card rounded-xl p-6">
      <div class="h-5 w-48 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="space-y-5">
        <div>
          <div class="h-3.5 w-40 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-3 w-64 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div>
          <div class="h-3.5 w-32 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-20 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          <div class="h-3 w-40 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div class="grid grid-cols-2 gap-5">
          <div>
            <div class="h-3.5 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
            <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          </div>
          <div>
            <div class="h-3.5 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
            <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card rounded-xl p-6">
      <div class="h-5 w-52 bg-gray-200 dark:bg-gray-700 rounded-md mb-5"></div>
      <div class="space-y-5">
        <div>
          <div class="h-3.5 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        </div>
        <div>
          <div class="h-3.5 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-16 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        </div>
        <div>
          <div class="h-3.5 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

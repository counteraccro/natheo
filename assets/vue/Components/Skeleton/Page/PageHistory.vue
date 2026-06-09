<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonPageHistory',
});
</script>

<template>
  <div class="animate-pulse">
    <div class="card rounded-xl p-6">
      <div class="h-5 w-52 bg-gray-200 dark:bg-gray-700 rounded-md mb-2"></div>
      <div class="h-3.5 w-80 bg-gray-200 dark:bg-gray-700 rounded mb-6"></div>
      <div class="space-y-2">
        <div class="flex items-center justify-between p-4 rounded-xl" style="background-color: var(--bg-hover)">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl shrink-0"></div>
            <div class="space-y-2">
              <div class="h-3.5 w-36 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div class="h-3 w-56 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
          </div>
          <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl" style="background-color: var(--bg-hover)">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl shrink-0"></div>
            <div class="space-y-2">
              <div class="h-3.5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div class="h-3 w-52 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
          </div>
          <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl" style="background-color: var(--bg-hover)">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl shrink-0"></div>
            <div class="space-y-2">
              <div class="h-3.5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div class="h-3 w-48 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
          </div>
          <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl" style="background-color: var(--bg-hover)">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl shrink-0"></div>
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <div class="h-3.5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-5 w-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
              </div>
              <div class="h-3 w-52 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
          </div>
          <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

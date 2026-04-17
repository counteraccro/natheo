<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton de la modération des commentaires
 */

import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonCommentModeration',
});
</script>

<template>
  <div
    class="rounded-2xl overflow-hidden animate-pulse"
    style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 px-5 py-3.5" style="border-bottom: 1px solid var(--border-color)">
      <div class="w-4 h-4 rounded flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
      <div class="h-4 flex-1 rounded-md bg-gray-200 dark:bg-gray-700"></div>
      <div class="h-6 w-20 rounded-full flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
    </div>
    <!-- Body -->
    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-(--border-color)">
      <div class="p-5 flex flex-col gap-3">
        <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-3 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-full rounded-md bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-4/5 rounded-md bg-gray-200 dark:bg-gray-700"></div>
      </div>
      <div class="p-5 flex flex-col gap-3">
        <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-32 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-28 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-16 w-full rounded-xl bg-gray-200 dark:bg-gray-700"></div>
      </div>
    </div>
  </div>

  <div
    class="rounded-2xl overflow-hidden animate-pulse mt-2"
    style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 px-5 py-3.5" style="border-bottom: 1px solid var(--border-color)">
      <div class="w-4 h-4 rounded flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
      <div class="h-4 flex-1 rounded-md bg-gray-200 dark:bg-gray-700"></div>
      <div class="h-6 w-20 rounded-full flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
    </div>
    <!-- Body -->
    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-(--border-color)">
      <div class="p-5 flex flex-col gap-3">
        <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-3 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-full rounded-md bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-4/5 rounded-md bg-gray-200 dark:bg-gray-700"></div>
      </div>
      <div class="p-5 flex flex-col gap-3">
        <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-32 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-4 w-28 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-16 w-full rounded-xl bg-gray-200 dark:bg-gray-700"></div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

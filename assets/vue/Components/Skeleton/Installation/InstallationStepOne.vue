<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonInstallationStepOne',
});
</script>

<template>
  <div class="animate-pulse" role="status" aria-label="Chargement">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
      <div class="form-group">
        <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700 mb-2"></div>
        <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700"></div>
      </div>
      <div class="form-group">
        <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700 mb-2"></div>
        <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700"></div>
      </div>
      <div class="form-group">
        <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700 mb-2"></div>
        <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700"></div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div class="form-group">
        <div class="h-3 w-20 rounded bg-gray-200 dark:bg-gray-700 mb-2"></div>
        <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700"></div>
      </div>
      <div class="form-group">
        <div class="h-3 w-12 rounded bg-gray-200 dark:bg-gray-700 mb-2"></div>
        <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700"></div>
      </div>
    </div>

    <div class="flex items-center justify-end mt-6">
      <div class="h-9 w-40 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
    </div>

    <span class="sr-only">Chargement...</span>
  </div>
</template>

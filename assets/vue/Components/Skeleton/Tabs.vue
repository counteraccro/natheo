<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonTabs',
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
  <div role="status" class="animate-pulse card rounded-lg mb-6">
    <!-- Filters -->
    <div class="border-b" style="border-color: var(--border-color)">
      <div class="flex flex-wrap gap-4 px-4 sm:px-6 py-4">
        <div class="h-6 bg-gray-200 rounded-full dark:bg-gray-700 w-16"></div>
        <div class="h-6 bg-gray-200 rounded-full dark:bg-gray-700 w-24"></div>
        <div class="h-6 bg-gray-200 rounded-full dark:bg-gray-700 w-32"></div>
        <div class="h-6 bg-gray-200 rounded-full dark:bg-gray-700 w-20"></div>
        <div class="h-6 bg-gray-200 rounded-full dark:bg-gray-700 w-28"></div>
      </div>
    </div>

    <!-- Liste des notifications - Skeleton -->
    <div>
      <!-- Notification Skeleton 1 -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-40"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-20"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
            <div class="flex items-center gap-2 mt-3">
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-32"></div>
              <div class="w-1 h-1 bg-gray-200 rounded-full dark:bg-gray-700"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-28"></div>
            </div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 2 -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-32"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-20"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-2/3"></div>
            <div class="flex items-center gap-2 mt-3">
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-28"></div>
              <div class="w-1 h-1 bg-gray-200 rounded-full dark:bg-gray-700"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-32"></div>
            </div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 3 -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-36"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-20"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-4/5"></div>
            <div class="flex items-center gap-2 mt-3">
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-24"></div>
              <div class="w-1 h-1 bg-gray-200 rounded-full dark:bg-gray-700"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-28"></div>
            </div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 4 -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-44"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-20"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-1/2"></div>
            <div class="flex items-center gap-2 mt-3">
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-20"></div>
              <div class="w-1 h-1 bg-gray-200 rounded-full dark:bg-gray-700"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-28"></div>
            </div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 5 -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-48"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-20"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-5/6"></div>
            <div class="flex items-center gap-2 mt-3">
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-32"></div>
              <div class="w-1 h-1 bg-gray-200 rounded-full dark:bg-gray-700"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-24"></div>
            </div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 6 (read) -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-40"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-24"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-2/3"></div>
            <div class="flex items-center gap-2 mt-3">
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-24"></div>
            </div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 7 (read) -->
      <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-32"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-24"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-1/2"></div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Notification Skeleton 8 (read) -->
      <div class="p-4 sm:p-6">
        <div class="flex items-start gap-3">
          <div class="w-4 h-4 bg-gray-200 rounded dark:bg-gray-700 mt-1"></div>
          <div class="w-10 h-10 bg-gray-200 rounded-full dark:bg-gray-700 flex-shrink-0"></div>

          <div class="flex-1 min-w-0 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-44"></div>
              <div class="h-3 bg-gray-200 rounded dark:bg-gray-700 w-24"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
          </div>

          <div class="w-8 h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
        </div>
      </div>
    </div>

    <span class="sr-only">Chargement...</span>
  </div>
</template>

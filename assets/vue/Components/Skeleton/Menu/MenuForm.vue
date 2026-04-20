<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonFormMenu',
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
  <div
    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mb-5 overflow-hidden"
  >
    <!-- Card header -->
    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="h-4 w-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      <div class="h-4 w-40 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
    </div>
    <!-- Card body -->
    <div class="p-5">
      <!-- Sous-header Données globales -->
      <div class="flex items-center gap-2 px-4 py-3 rounded-lg bg-gray-100 dark:bg-gray-700 mb-5">
        <div class="h-4 w-4 rounded bg-gray-300 dark:bg-gray-600 animate-pulse"></div>
        <div class="h-4 w-32 rounded bg-gray-300 dark:bg-gray-600 animate-pulse"></div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Colonne gauche -->
        <div class="space-y-5">
          <!-- Nom -->
          <div>
            <div class="h-3.5 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>
            <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          </div>
          <!-- Comportement -->
          <div>
            <div class="h-3.5 w-28 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>
            <div class="flex gap-6">
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-3.5 w-28 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-3.5 w-28 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              </div>
            </div>
            <div class="h-3 w-72 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mt-2"></div>
          </div>
          <!-- État -->
          <div>
            <div class="h-3.5 w-12 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>
            <div class="flex gap-6">
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-3.5 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-3.5 w-28 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              </div>
            </div>
            <div class="h-3 w-48 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mt-2"></div>
          </div>
        </div>
        <!-- Colonne droite -->
        <div class="space-y-5">
          <div>
            <div class="h-3.5 w-32 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>
            <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          </div>
          <div>
            <div class="h-3.5 w-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>
            <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

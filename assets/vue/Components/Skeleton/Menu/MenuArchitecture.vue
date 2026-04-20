<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonArchitectureMenu',
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
    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden"
  >
    <!-- Card header -->
    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="h-4 w-4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
      <div class="h-4 w-44 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
    </div>
    <!-- Card body -->
    <div class="p-4">
      <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
        <!-- ARBRE (2/5) -->
        <div class="xl:col-span-2">
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
            <!-- Header arbre -->
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700">
              <div class="h-4 w-4 rounded bg-gray-300 dark:bg-gray-600 animate-pulse"></div>
              <div class="h-4 w-40 rounded bg-gray-300 dark:bg-gray-600 animate-pulse"></div>
            </div>
            <!-- Nodes -->
            <div class="p-3 space-y-2">
              <!-- Node racine 1 avec enfants -->
              <div class="space-y-2">
                <div class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                  <div class="w-3.5 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="h-3.5 flex-1 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="h-4 w-12 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="flex gap-1">
                    <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  </div>
                </div>
                <!-- Enfants niveau 2 -->
                <div class="ml-5 pl-3 border-l-2 border-dashed border-gray-200 dark:border-gray-700 space-y-2">
                  <div class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="w-3.5 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-3.5 flex-1 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-4 w-10 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="flex gap-1">
                      <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    </div>
                  </div>
                  <!-- Enfant niveau 3 -->
                  <div class="ml-5 pl-3 border-l-2 border-dashed border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                      <div class="w-3.5 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="h-3.5 flex-1 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="h-4 w-8 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      <div class="flex gap-1">
                        <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                        <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                        <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                        <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Node racine 2 -->
              <div class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="w-3.5 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-3.5 flex-1 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-4 w-14 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="flex gap-1">
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
              </div>

              <!-- Node racine 3 -->
              <div class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="w-3.5 h-3.5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-3.5 flex-1 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-4 w-8 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="flex gap-1">
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
              </div>

              <!-- Bouton Nouveau -->
              <div class="h-9 w-full rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse mt-3"></div>
            </div>

            <!-- Légende -->
            <div class="px-3 pb-3 pt-2 border-t border-gray-200 dark:border-gray-700">
              <div class="h-3 w-10 rounded bg-gray-200 dark:bg-gray-700 animate-pulse mb-2"></div>
              <div class="flex gap-1.5">
                <div class="h-4 w-10 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-4 w-8 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-4 w-14 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                <div class="h-4 w-10 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- PANNEAU ÉDITION (3/5) -->
        <div class="xl:col-span-3">
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700">
              <div class="h-4 w-4 rounded bg-gray-300 dark:bg-gray-600 animate-pulse"></div>
              <div class="h-4 w-48 rounded bg-gray-300 dark:bg-gray-600 animate-pulse"></div>
            </div>
            <!-- Corps -->
            <div class="flex flex-col items-center justify-center py-12 px-6 gap-4">
              <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              <div class="h-4 w-48 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              <div class="h-3 w-64 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
              <div class="space-y-2.5 mt-2 w-full max-w-xs">
                <div class="flex items-center gap-2">
                  <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="h-3 w-40 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="h-3 w-36 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="h-3 w-44 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                  <div class="h-3 w-32 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

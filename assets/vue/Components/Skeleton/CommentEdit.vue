<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonCommentEdit',
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
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Colonne gauche -->
    <div class="xl:col-span-2 flex flex-col gap-6">
      <!-- Carte éditeur -->
      <div
        class="rounded-lg overflow-hidden animate-pulse"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
      >
        <!-- Header carte -->
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid var(--border-color)">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-4 w-40 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          </div>
          <div class="h-6 w-20 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <!-- Toolbar skeleton -->
        <div
          class="flex items-center gap-2 px-4 py-3"
          style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color)"
        >
          <div class="h-7 w-14 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          <div class="ml-auto flex gap-2">
            <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-7 w-7 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
          </div>
        </div>
        <!-- Zone texte skeleton -->
        <div class="p-5 flex flex-col gap-3">
          <div class="h-4 w-full rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-5/6 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-4/6 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-full rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-3/6 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-5/6 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-full rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-4 w-2/6 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <!-- Footer éditeur skeleton -->
        <div class="flex items-center justify-between px-5 py-3" style="border-top: 1px solid var(--border-color)">
          <div class="h-3 w-52 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-3 w-32 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Barre d'actions skeleton -->
      <div
        class="flex items-center justify-between px-6 py-4 rounded-lg animate-pulse"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
      >
        <div class="h-9 w-28 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-9 w-52 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
      </div>
    </div>

    <!-- Colonne droite -->
    <div class="flex flex-col gap-6">
      <!-- Carte Statut skeleton -->
      <div
        class="rounded-lg overflow-hidden animate-pulse"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
      >
        <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
          <div class="h-4 w-20 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="p-5 flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <div class="h-7 w-20 rounded-full bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-4 w-20 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          </div>
          <div class="h-3 w-28 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          <div class="h-10 w-full rounded-lg bg-gray-200 dark:bg-gray-700"></div>
        </div>
      </div>

      <!-- Carte Informations skeleton -->
      <div
        class="rounded-lg overflow-hidden animate-pulse"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
      >
        <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
          <div class="h-4 w-28 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <!-- 4 lignes d'info -->
        <div class="divide-y divide-[var(--border-color)]">
          <div v-for="i in 4" class="flex items-center gap-3 px-5 py-4">
            <div class="w-8 h-8 rounded-lg flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex flex-col gap-1.5 flex-1">
              <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
              <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
          <div class="flex items-center gap-3 px-5 py-4">
            <div class="w-8 h-8 rounded-lg flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex flex-col gap-1.5 flex-1">
              <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
              <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
          <div class="flex items-center gap-3 px-5 py-4">
            <div class="w-8 h-8 rounded-lg flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex flex-col gap-1.5 flex-1">
              <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
              <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
          <div class="flex items-center gap-3 px-5 py-4">
            <div class="w-8 h-8 rounded-lg flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex flex-col gap-1.5 flex-1">
              <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
              <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
          <div class="flex items-center gap-3 px-5 py-4">
            <div class="w-8 h-8 rounded-lg flex-shrink-0 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex flex-col gap-1.5 flex-1">
              <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
              <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Carte Article associé skeleton -->
      <div
        class="rounded-lg overflow-hidden animate-pulse"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm)"
      >
        <div class="px-5 py-4" style="border-bottom: 1px solid var(--border-color)">
          <div class="h-4 w-32 rounded-md bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="p-5 flex items-start gap-3">
          <div class="w-8 h-8 rounded-lg flex-shrink-0 mt-0.5 bg-gray-200 dark:bg-gray-700"></div>
          <div class="flex flex-col gap-2 flex-1">
            <div class="h-4 w-full rounded-md bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-3 w-24 rounded-md bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-3 w-20 rounded-md bg-gray-200 dark:bg-gray-700"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

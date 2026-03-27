<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton de l'upload d'un document
 */
export default {
  name: 'SkeletonMediathequeUpload',
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
  <div class="animate-pulse">
    <!-- Zone de dépôt -->
    <div
      class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed mb-4"
      style="border-color: var(--border-color); background-color: var(--bg-main); padding: 1.5rem 1rem"
    >
      <!-- Icône -->
      <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>

      <!-- Texte principal -->
      <div class="flex flex-col items-center gap-2">
        <div class="w-32 h-3 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="w-20 h-3 rounded bg-gray-200 dark:bg-gray-700"></div>
      </div>

      <!-- Info taille/type -->
      <div class="w-40 h-2.5 rounded bg-gray-200 dark:bg-gray-700"></div>
    </div>
  </div>
</template>

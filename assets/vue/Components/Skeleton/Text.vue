<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Skeleton d'un texte
 */
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonText',
  props: {
    nbParagraphe: {
      type: Number,
      default: 1,
    },
  },
});
</script>

<template>
  <div role="status" class="animate-pulse space-y-4">
    <!-- Titre skeleton -->
    <div class="h-4 bg-gray-300 rounded-full dark:bg-gray-600 w-48"></div>

    <!-- Paragraphe skeleton -->
    <div v-for="index in nbParagraphe" :key="index" class="space-y-2.5">
      <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-full"></div>
      <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-full"></div>
      <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-3/4"></div>
    </div>
  </div>
</template>

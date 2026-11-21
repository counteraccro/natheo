<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tags d'une page : pastille pleine de leur couleur
 */
import type { Tag } from '@/ts/Front/Api/Page.type';

defineProps<{ tags: Tag[] }>();
</script>

<template>
  <ul v-if="tags.length" class="flex flex-wrap gap-2">
    <li
      v-for="tag in tags"
      :key="tag.id"
      class="px-2 py-0.5 text-sm rounded text-white"
      :style="{ backgroundColor: tag.color }"
    >
      {{ tag.label }}
    </li>
  </ul>
</template>

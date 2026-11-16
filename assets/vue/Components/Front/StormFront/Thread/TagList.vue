<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tags d'une page : carré de leur couleur suivi du libellé
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Tag } from '@/ts/Front/Api/Page.type';

defineProps<{ tags: Tag[] }>();
const { translate } = useFrontContext();
</script>

<template>
  <ul v-if="tags.length" class="flex flex-wrap gap-x-4 gap-y-1 text-[0.88rem]" :aria-label="translate.main.tagsLabel">
    <li v-for="tag in tags" :key="tag.id" class="inline-flex items-center gap-1.5">
      <span class="size-2.25 rounded-[1px]" :style="{ backgroundColor: tag.color }" aria-hidden="true" />
      {{ tag.label }}
    </li>
  </ul>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tags d'une page sur le bandeau sombre : carré de leur couleur suivi du libellé
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Tag } from '@/ts/Front/Api/Page.type';

defineProps<{ tags: Tag[] }>();
const { translate } = useFrontContext();
</script>

<template>
  <ul v-if="tags.length" class="flex flex-wrap gap-2" :aria-label="translate.main.tagsLabel">
    <li
      v-for="tag in tags"
      :key="tag.id"
      class="inline-flex items-center gap-2 rounded-nh bg-white/8 px-2.5 py-1 text-[0.88rem] font-medium"
    >
      <span class="size-2.5 rounded-[2px]" :style="{ backgroundColor: tag.color }" aria-hidden="true" />
      {{ tag.label }}
    </li>
  </ul>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un tag dans le thème, affiché par le formulaire des tags : carré de couleur et libellé, comme dans l'en-tête d'une page
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useTagPreview } from '@/vue/Composables/Front/useTagPreview';
import TagList from '@/vue/Components/Front/StormFront/Thread/TagList.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const tags = useTagPreview();
</script>

<template>
  <div class="p-5">
    <TagList :tags="tags" />
  </div>
</template>

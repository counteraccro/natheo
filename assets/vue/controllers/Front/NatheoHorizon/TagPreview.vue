<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un tag dans le thème, affiché par le formulaire des tags : sur le bandeau sombre de la page
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useTagPreview } from '@/vue/Composables/Front/useTagPreview';
import TagList from '@/vue/Components/Front/NatheoHorizon/Page/TagList.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const tags = useTagPreview();
</script>

<template>
  <div class="bg-night p-5 text-on-night">
    <TagList :tags="tags" />
  </div>
</template>

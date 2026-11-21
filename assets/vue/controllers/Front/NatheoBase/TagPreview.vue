<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un tag dans le thème, affiché par le formulaire des tags : pastille pleine, comme sous une page
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useTagPreview } from '@/vue/Composables/Front/useTagPreview';
import TagList from '@/vue/Components/Front/NatheoBase/TagList.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const tags = useTagPreview();
</script>

<template>
  <div class="p-4">
    <TagList :tags="tags" />
  </div>
</template>

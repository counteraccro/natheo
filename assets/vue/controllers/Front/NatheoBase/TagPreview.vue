<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un tag dans le thème, affiché par le formulaire des tags : pastille pleine, comme sous une page
 */
import { computed } from 'vue';
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { usePreviewFrame } from '@/vue/Composables/Front/usePreviewFrame';
import TagList from '@/vue/Components/Front/NatheoBase/TagList.vue';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';
import type { Tag } from '@/ts/Front/Api/Page.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const tag = usePreviewFrame<Tag>();
const tags = computed(() => (tag.value === null ? [] : [tag.value]));
</script>

<template>
  <div class="p-4">
    <TagList :tags="tags" />
  </div>
</template>

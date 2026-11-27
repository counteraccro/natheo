<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Page d'un commentaire, colonne de la liste : lien qui filtre la liste sur cette page
 */
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { AdminCommentPage } from '@/ts/Admin/Api/AdminComment.type';

const props = defineProps<{ value: unknown }>();

const { t } = useAdminContext();
const route = useRoute();
const page = computed(() => props.value as AdminCommentPage | null);
// Nouveau filtre : la liste repart de sa première page
const to = computed(() => {
  const { page: _page, ...query } = route.query;
  return { name: route.meta.screenGroup, query: { ...query, pageId: String(page.value?.id ?? '') } };
});
</script>

<template>
  <RouterLink
    v-if="page"
    :to="to"
    :title="t('comment.collection.page_filter_title')"
    class="ui-ring relative z-10 rounded-sm font-semibold text-primary underline decoration-transparent underline-offset-3 hover:decoration-current"
  >
    {{ page.title }}
  </RouterLink>
  <template v-else>—</template>
</template>

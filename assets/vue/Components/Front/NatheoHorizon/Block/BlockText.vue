<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc texte, HTML rendu par l'API
 */
import type { TextContent } from '@/ts/Front/Api/PageContent.type';

defineProps<{ content: TextContent }>();
</script>

<template>
  <div class="nh-prose" v-html="content.html" />
</template>

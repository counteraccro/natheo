<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc texte du thème Natheo-Base, HTML rendu par l'API
 */
import type { TextContent } from '@/ts/Front/Api/PageContent.type';

defineProps<{ content: TextContent }>();
</script>

<template>
  <div class="natheo-base-content" v-html="content.html"></div>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paragraphe du tutoriel : les passages entre accents graves s'affichent en code, sans HTML dans les traductions
 */
import { computed } from 'vue';

const props = defineProps<{ text: string }>();

const parts = computed(() => props.text.split('`').map((text, index) => ({ text, code: index % 2 === 1 })));
</script>

<template>
  <p class="m-0 leading-relaxed text-fg">
    <template v-for="(part, index) in parts" :key="index">
      <code v-if="part.code" class="rounded-sm bg-surface px-1 py-px font-mono text-[0.9em] break-words">{{
        part.text
      }}</code>
      <template v-else>{{ part.text }}</template>
    </template>
  </p>
</template>

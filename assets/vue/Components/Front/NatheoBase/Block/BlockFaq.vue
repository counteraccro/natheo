<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc FAQ du thème Natheo-Base : questions dépliables par catégorie
 */
import type { FaqContent } from '@/ts/Front/Api/PageContent.type';

defineProps<{ content: FaqContent }>();
</script>

<template>
  <div class="space-y-4">
    <h2 class="text-2xl font-semibold">{{ content.title }}</h2>
    <section v-for="category in content.categories" :key="category.title" class="space-y-2">
      <h3 class="text-xl font-semibold">{{ category.title }}</h3>
      <details v-for="question in category.questions" :key="question.title" class="border border-gray-200 p-3">
        <summary class="cursor-pointer font-medium">{{ question.title }}</summary>
        <div class="natheo-base-content mt-2" v-html="question.answer"></div>
      </details>
    </section>
  </div>
</template>

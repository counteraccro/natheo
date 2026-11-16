<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc FAQ : questions dépliables par catégorie
 */
import FrontIcon from '@/vue/Components/Front/Global/FrontIcon.vue';
import type { FaqContent } from '@/ts/Front/Api/PageContent.type';

defineProps<{ content: FaqContent }>();
</script>

<template>
  <div class="max-w-180">
    <h2 class="mb-4 text-[clamp(1.7rem,2.6vw,2.15rem)]">{{ content.title }}</h2>
    <template v-for="category in content.categories" :key="category.title">
      <h3 class="mt-6 mb-1.5 text-[0.95rem] font-semibold tracking-normal text-fg-muted">{{ category.title }}</h3>
      <details v-for="question in category.questions" :key="question.title" class="group border-t border-line last:border-b">
        <summary
          class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 text-[1.1rem] font-semibold [&::-webkit-details-marker]:hidden"
        >
          {{ question.title }}
          <FrontIcon name="plus" class="size-5 text-primary transition-transform group-open:rotate-45" />
        </summary>
        <div class="sf-prose pb-4 text-[1.05rem] text-fg-muted" v-html="question.answer" />
      </details>
    </template>
  </div>
</template>

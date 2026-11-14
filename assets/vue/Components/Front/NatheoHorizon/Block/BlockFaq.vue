<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc FAQ : questions dépliables par catégorie
 */
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import type { FaqContent } from '@/ts/Front/Api/PageContent.type';

defineProps<{ content: FaqContent }>();
</script>

<template>
  <div>
    <h2 class="mb-4 text-[clamp(1.5rem,2.4vw,2rem)]">{{ content.title }}</h2>
    <template v-for="category in content.categories" :key="category.title">
      <h3 class="mt-6 mb-2 text-base font-bold tracking-normal text-fg-muted">{{ category.title }}</h3>
      <details
        v-for="question in category.questions"
        :key="question.title"
        class="group border-t border-line last:border-b"
      >
        <summary
          class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 font-semibold [&::-webkit-details-marker]:hidden"
        >
          {{ question.title }}
          <span class="grid size-6.5 shrink-0 place-items-center rounded-nh bg-surface text-primary transition-transform group-open:rotate-45">
            <HorizonIcon name="plus" class="size-4" />
          </span>
        </summary>
        <div class="nh-prose pb-4 text-fg-muted" v-html="question.answer" />
      </details>
    </template>
  </div>
</template>

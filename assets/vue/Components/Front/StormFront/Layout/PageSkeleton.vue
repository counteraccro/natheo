<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page en cours de chargement : formes du titre et du texte
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

const { translate } = useFrontContext();
</script>

<template>
  <div class="px-[clamp(1.25rem,4vw,4.5rem)] pt-12 pb-20" aria-busy="true">
    <p class="sr-only">{{ translate.main.loading }}</p>
    <div class="max-w-190" aria-hidden="true">
      <div class="h-13 w-11/12 rounded-sf bg-thread motion-safe:animate-breathe" />
      <div class="mt-3 h-13 w-3/5 rounded-sf bg-thread motion-safe:animate-breathe" />
      <div class="mt-6 h-3.5 w-1/2 rounded-sf bg-thread motion-safe:animate-breathe" />
      <div v-for="line in 6" :key="line" class="mt-4 h-4 rounded-sf bg-thread motion-safe:animate-breathe first-of-type:mt-14" />
    </div>
  </div>
</template>

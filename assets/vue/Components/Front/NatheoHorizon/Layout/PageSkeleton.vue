<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Squelette affiché pendant le chargement de la page
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

const { translate } = useFrontContext();
</script>

<template>
  <div aria-busy="true">
    <section class="bg-night pt-10 pb-16">
      <div class="mx-auto grid w-full max-w-page gap-4 px-5">
        <div class="h-14 w-[min(640px,90%)] rounded-nh bg-white/10 motion-safe:animate-breathe" />
        <div class="h-14 w-[min(420px,60%)] rounded-nh bg-white/10 motion-safe:animate-breathe" />
        <div class="mt-4 h-4.5 w-[min(380px,70%)] rounded-nh bg-white/10 motion-safe:animate-breathe" />
      </div>
    </section>
    <div class="mx-auto grid w-full max-w-page gap-4 px-5 pt-16 pb-20">
      <div class="h-1 w-14 rounded-nh bg-surface motion-safe:animate-breathe" />
      <div class="h-7.5 w-2/5 rounded-nh bg-surface motion-safe:animate-breathe" />
      <div class="h-4.5 w-[min(640px,90%)] rounded-nh bg-surface motion-safe:animate-breathe" />
      <div class="h-4.5 w-[min(560px,80%)] rounded-nh bg-surface motion-safe:animate-breathe" />
    </div>
    <p class="sr-only" role="status">{{ translate.main.loading }}</p>
  </div>
</template>

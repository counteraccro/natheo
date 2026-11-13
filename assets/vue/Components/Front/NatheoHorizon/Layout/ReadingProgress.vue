<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Barre de lecture en haut de l'écran, décorative
 */
import { useReadingProgress } from '@/vue/Composables/Front/useReadingProgress';

const progress = useReadingProgress();
</script>

<template>
  <div class="fixed inset-x-0 top-0 z-50 h-1 bg-white/15" aria-hidden="true">
    <span
      class="block h-full origin-left bg-linear-to-r from-mod-full via-mod-half to-mod-third"
      :style="{ transform: `scaleX(${progress})` }"
    />
  </div>
</template>

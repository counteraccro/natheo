<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bouton de bascule entre le mode clair et le mode sombre
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useColorScheme } from '@/vue/Composables/Front/useColorScheme';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';

const { translate } = useFrontContext();
const { scheme, toggle } = useColorScheme();
</script>

<template>
  <button
    type="button"
    class="inline-flex size-11 cursor-pointer items-center justify-center rounded-nh hover:bg-white/10"
    :aria-pressed="scheme === 'dark'"
    @click="toggle"
  >
    <span class="sr-only">{{ translate.header.darkMode }}</span>
    <HorizonIcon :name="scheme === 'dark' ? 'sun' : 'moon'" class="size-5.5" />
  </button>
</template>

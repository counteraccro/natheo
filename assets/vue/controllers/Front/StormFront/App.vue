<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant racine du thème StormFront : installe le contexte du site public et compose le lecteur
 */
import { createFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { usePage } from '@/vue/Composables/Front/usePage';
import type { FrontThemeProps } from '@/ts/Front/FrontProps.type';

const props = defineProps<FrontThemeProps>();
createFrontContext(props);
const { data: page } = usePage();
</script>

<template>
  <main id="contenu" tabindex="-1" class="outline-none">
    <h1 v-if="page" class="p-10 text-5xl">{{ page.title }}</h1>
  </main>
</template>

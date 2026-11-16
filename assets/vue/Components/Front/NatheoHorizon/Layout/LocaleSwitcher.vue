<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Choix de la langue dans l'en-tête, sur grand écran
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useLanguages } from '@/vue/Composables/Front/useLanguages';
import NavPanel from '@/vue/Components/Front/NatheoHorizon/Layout/NavPanel.vue';

const { datas, translate } = useFrontContext();
const languages = useLanguages();
</script>

<template>
  <NavPanel align="right">
    <template #label>
      <span class="sr-only">{{ translate.header.localeLabel }} :</span>
      {{ datas.locale.toUpperCase() }}
    </template>
    <ul class="min-w-43 p-1.5">
      <li v-for="language in languages" :key="language.locale">
        <a
          :href="language.url"
          :lang="language.locale"
          :hreflang="language.locale"
          :aria-current="language.current ? 'true' : undefined"
          class="flex min-h-11 items-center gap-2.5 rounded-nh px-3 text-fg no-underline hover:bg-surface"
        >
          <span v-if="language.current" class="size-2 rounded-[2px] bg-primary" aria-hidden="true" />
          {{ language.label }}
        </a>
      </li>
    </ul>
  </NavPanel>
</template>

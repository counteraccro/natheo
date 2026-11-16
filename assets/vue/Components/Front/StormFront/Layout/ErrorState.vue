<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Erreur de chargement de la page : message traduit et retour à l'accueil
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

const props = defineProps<{ error: unknown }>();
const context = useFrontContext();
</script>

<template>
  <div class="max-w-150 px-[clamp(1.25rem,4vw,4.5rem)] pt-20 pb-20" role="alert">
    <h1 class="text-[clamp(2.2rem,4.2vw,3.5rem)] leading-[1.05]">{{ context.errorLabel(props.error) }}</h1>
    <a
      :href="context.urls.home[context.datas.locale]"
      class="mt-8 inline-flex min-h-11 items-center rounded-sf bg-primary px-4 font-semibold text-on-primary no-underline hover:brightness-110"
    >
      {{ context.translate.errorApi.back }}
    </a>
  </div>
</template>

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
  <section class="bg-night pt-10 pb-16 text-on-night [--focus:var(--focus-on-night)]" role="alert">
    <div class="mx-auto w-full max-w-page px-5">
      <h1 class="max-w-[16ch] text-[clamp(2.4rem,5.2vw,4.25rem)] leading-[1.02]">{{ context.errorLabel(props.error) }}</h1>
      <a
        :href="context.urls.home[context.datas.locale]"
        class="mt-8 inline-flex min-h-11 items-center rounded-nh bg-white px-4 font-semibold text-night no-underline hover:bg-mod-full"
      >
        {{ context.translate.errorApi.back }}
      </a>
    </div>
  </section>
</template>

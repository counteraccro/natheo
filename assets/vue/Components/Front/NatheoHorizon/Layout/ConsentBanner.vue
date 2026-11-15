<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bandeau de consentement du thème Natheo-Horizon : texte du site et deux boutons de même importance
 */
import { nextTick, ref, watch } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useConsent } from '@/vue/Composables/Front/useConsent';

const { datas, translate } = useFrontContext();
const consent = useConsent();
const region = ref<HTMLElement | null>(null);

// Rouvert depuis le pied de page : le focus suit, pour le clavier et les lecteurs d'écran
watch(consent.visible, async (visible, wasVisible) => {
  if (visible && !wasVisible) {
    await nextTick();
    region.value?.focus();
  }
});

const button =
  'inline-flex min-h-11 flex-1 cursor-pointer items-center justify-center rounded-nh px-4 font-semibold text-on-night shadow-[inset_0_0_0_1.5px_rgb(201_184_255/0.6)] hover:shadow-[inset_0_0_0_1.5px_#fff] sm:flex-none';
</script>

<template>
  <section
    v-if="consent.visible.value && datas.consent"
    ref="region"
    tabindex="-1"
    :aria-label="translate.consent.regionLabel"
    class="fixed inset-x-0 bottom-0 z-90 bg-night p-5 text-on-night shadow-[0_-8px_30px_rgb(19_17_42/0.35)] outline-none [--focus:var(--focus-on-night)] sm:inset-x-auto sm:bottom-5 sm:left-5 sm:max-w-md sm:rounded-nh-lg"
  >
    <div
      v-if="datas.consent.text"
      class="text-[0.95rem] leading-relaxed text-on-night-muted [&_a]:text-on-night [&_a]:underline [&_p+p]:mt-2"
      v-html="datas.consent.text"
    />
    <div class="mt-4 flex flex-wrap gap-2">
      <button type="button" :class="button" @click="consent.accept()">
        {{ datas.consent.accept || translate.consent.accept }}
      </button>
      <button type="button" :class="button" @click="consent.refuse()">
        {{ datas.consent.refuse || translate.consent.refuse }}
      </button>
    </div>
  </section>
</template>

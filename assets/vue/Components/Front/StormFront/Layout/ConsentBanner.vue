<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bandeau de consentement du thème StormFront : texte du site et deux boutons de même importance
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useConsent } from '@/vue/Composables/Front/useConsent';

const { datas, translate } = useFrontContext();
const consent = useConsent();

const BUTTON =
  'inline-flex min-h-11 flex-1 cursor-pointer items-center justify-center rounded-sf px-4 font-semibold text-on-rail shadow-[inset_0_0_0_1.5px_var(--on-rail-muted)] hover:shadow-[inset_0_0_0_1.5px_var(--on-rail)] sm:flex-none';
</script>

<template>
  <section
    v-if="consent.visible.value && datas.consent"
    :ref="(element) => (consent.region.value = element as HTMLElement | null)"
    tabindex="-1"
    :aria-label="translate.consent.regionLabel"
    class="fixed inset-x-0 bottom-16 z-90 bg-rail p-5 text-on-rail shadow-[0_-8px_30px_rgb(13_17_20/0.35)] outline-none [--focus:var(--bolt)] md:inset-x-auto md:right-5 md:bottom-5 md:max-w-md md:rounded-sf"
  >
    <div
      v-if="datas.consent.text"
      class="text-[0.95rem] leading-relaxed text-on-rail-muted [&_a]:text-on-rail [&_a]:underline [&_p+p]:mt-2"
      v-html="datas.consent.text"
    />
    <div class="mt-4 flex flex-wrap gap-2">
      <button type="button" :class="BUTTON" @click="consent.accept()">
        {{ datas.consent.accept || translate.consent.accept }}
      </button>
      <button type="button" :class="BUTTON" @click="consent.refuse()">
        {{ datas.consent.refuse || translate.consent.refuse }}
      </button>
    </div>
  </section>
</template>

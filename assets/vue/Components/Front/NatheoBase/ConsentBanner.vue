<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bandeau de consentement du thème Natheo-Base : texte du site et deux boutons de même importance
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useConsent } from '@/vue/Composables/Front/useConsent';

const { datas, translate } = useFrontContext();
const consent = useConsent();

const button =
  'cursor-pointer rounded border border-gray-400 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-500 dark:hover:bg-gray-700';
</script>

<template>
  <section
    v-if="consent.visible.value && datas.consent"
    :ref="(element) => (consent.region.value = element as HTMLElement | null)"
    tabindex="-1"
    :aria-label="translate.consent.regionLabel"
    class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white p-4 text-sm text-gray-900 shadow-lg dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
  >
    <div class="mx-auto flex max-w-5xl flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div v-if="datas.consent.text" class="[&_a]:underline [&_p+p]:mt-2" v-html="datas.consent.text" />
      <div class="flex shrink-0 gap-2">
        <button type="button" :class="button" @click="consent.accept()">
          {{ datas.consent.accept || translate.consent.accept }}
        </button>
        <button type="button" :class="button" @click="consent.refuse()">
          {{ datas.consent.refuse || translate.consent.refuse }}
        </button>
      </div>
    </div>
  </section>
</template>

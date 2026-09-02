<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Installation CMS - Etape 4 résumé de fin d'installation
 */
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type {
  InstallationStepFourUrls,
  InstallationStepFourTranslate,
  InstallationStepFourLocales,
  InstallationStepFourDatas,
} from '@/ts/Installation/InstallationStepFour.type';

export default defineComponent({
  name: 'InstallationStepFour',
  props: {
    urls: {
      type: Object as PropType<InstallationStepFourUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<InstallationStepFourTranslate>,
      required: true,
    },
    locales: {
      type: Object as PropType<InstallationStepFourLocales>,
      required: true,
    },
    datas: {
      type: Object as PropType<InstallationStepFourDatas>,
      required: true,
    },
  },
});
</script>

<template>
  <div class="card rounded-xl overflow-hidden">
    <div class="px-6 pt-10 pb-8 text-center">
      <div
        class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5"
        style="background-color: var(--alert-success-bg)"
      >
        <svg class="w-8 h-8" style="color: var(--btn-success)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h2 class="text-2xl font-bold mb-2">{{ translate.title_h1 }}</h2>
      <p class="text-sm max-w-md mx-auto" style="color: var(--text-secondary)">{{ translate.description }}</p>
    </div>

    <div class="px-6 sm:px-10 pb-8">
      <div class="flex flex-col gap-2 max-w-md mx-auto">
        <div class="flex items-center gap-2.5 px-4 py-3 rounded-lg" style="background-color: var(--bg-hover)">
          <svg
            class="w-4 h-4 flex-shrink-0"
            style="color: var(--btn-success)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm">{{ translate.summary_database }}</span>
        </div>

        <div class="flex items-center gap-2.5 px-4 py-3 rounded-lg" style="background-color: var(--bg-hover)">
          <svg
            class="w-4 h-4 flex-shrink-0"
            style="color: var(--btn-success)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm">{{ translate.summary_user }}</span>
        </div>

        <div class="flex items-center gap-2.5 px-4 py-3 rounded-lg" style="background-color: var(--bg-hover)">
          <svg
            class="w-4 h-4 flex-shrink-0"
            style="color: var(--btn-success)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm">{{ translate.summary_data }}</span>
        </div>

        <div class="flex items-center gap-2.5 px-4 py-3 rounded-lg" style="background-color: var(--bg-hover)">
          <svg
            class="w-4 h-4 flex-shrink-0"
            style="color: var(--btn-success)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm">{{ translate.summary_cache }}</span>
        </div>
      </div>
    </div>

    <div class="px-6 sm:px-10 pb-10 flex justify-center">
      <a :href="urls.auth" class="btn btn-primary btn-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"
          />
        </svg>
        {{ translate.btn_login }}
      </a>
    </div>
  </div>
</template>

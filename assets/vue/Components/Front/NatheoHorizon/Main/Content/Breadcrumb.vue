<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fil d'ariane
 */
import { defineComponent, type PropType } from 'vue';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontPage } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'Breadcrumb',
  props: {
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    page: {
      type: Object as PropType<FrontPage>,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
  },
  computed: {
    homeUrl(): string {
      return `${this.utilsFront.datas.basePath}/${this.utilsFront.datas.locale}/`;
    },
  },
});
</script>

<template>
  <nav class="text-sm mb-3" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
      <li class="inline-flex items-center">
        <a :href="homeUrl" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
          <span>{{ label }}</span>
        </a>
      </li>

      <li aria-hidden="true" class="text-gray-400">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none">
          <path
            d="M7 6l5 4-5 4"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </li>

      <li class="inline-flex items-center" aria-current="page">
        <span class="text-gray-900 font-medium truncate">{{ page.title }}</span>
      </li>
    </ol>
  </nav>
</template>

<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fil d'ariane
 */
export default {
  name: 'Breadcrumb',
  props: {
    utilsFront: Object,
    page: Object,
    locale: String,
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    };
  },
  mounted() {},

  methods: {},
};
</script>

<template>
  <!-- Fil d'Ariane simple - Tailwind CSS 4 -->
  <nav class="text-sm mb-3" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
      <!-- Item 1 -->
      <li class="inline-flex items-center">
        <a :href="'/' + this.locale" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
          <span>Index</span>
        </a>
      </li>

      <!-- Séparateur -->
      <li aria-hidden="true" class="text-gray-400">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
          <path
            d="M7 6l5 4-5 4"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </li>

      <!-- Item courant (non cliquable) -->
      <li class="inline-flex items-center" aria-current="page">
        <span class="text-gray-900 font-medium truncate">{{ this.page.title }}</span>
      </li>
    </ol>
  </nav>
</template>

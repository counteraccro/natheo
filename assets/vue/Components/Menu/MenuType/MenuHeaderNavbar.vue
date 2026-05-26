<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData, MenuElement } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuHeaderNavbar',
  props: {
    menu: { type: Object as PropType<Menu>, required: true },
    locale: { type: String, required: true },
    data: { type: Object as PropType<LoadMenuData>, required: true },
  },

  methods: {
    getLabel(element: MenuElement): string {
      return element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.textLink ?? '';
    },
    getUrl(element: MenuElement): string {
      return element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.link ?? '#';
    },
  },
});
</script>

<template>
  <nav class="w-full rounded-xl border" style="background-color: var(--bg-card); border-color: var(--border-color)">
    <div class="px-4 py-3 flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center gap-2 font-bold text-sm" style="color: var(--text-primary)">
        <div
          class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
          style="background-color: var(--primary)"
        >
          N
        </div>
        {{ data.name }}
      </div>

      <!-- Liens -->
      <ul class="hidden md:flex items-center gap-1">
        <li v-for="element in menu.menuElements" :key="element.id" v-show="!element.disabled">
          <a
            :href="getUrl(element)"
            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
            style="color: var(--text-primary)"
            @click.prevent
          >
            {{ getLabel(element) }}
          </a>
        </li>
      </ul>

      <!-- Burger mobile -->
      <button class="md:hidden p-2 rounded-lg" style="color: var(--text-secondary)">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>
  </nav>
</template>

<style scoped></style>

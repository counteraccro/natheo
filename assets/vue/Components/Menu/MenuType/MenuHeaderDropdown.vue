<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData } from '@/ts/Menu/type';
import MenuHeaderDropdownItem from './MenuHeaderDropdownItem.vue';

export default defineComponent({
  name: 'MenuHeaderDropdown',
  components: { MenuHeaderDropdownItem },
  props: {
    menu: { type: Object as PropType<Menu>, required: true },
    locale: { type: String, required: true },
    data: { type: Object as PropType<LoadMenuData>, required: true },
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

      <!-- Nav avec dropdowns -->
      <ul class="hidden md:flex items-center gap-1">
        <MenuHeaderDropdownItem
          v-for="element in menu.menuElements"
          :key="element.id"
          :element="element"
          :locale="locale"
          :depth="0"
        />
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

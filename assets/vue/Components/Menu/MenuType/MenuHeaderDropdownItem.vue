<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MenuElement } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuHeaderDropdownItem',

  components: {
    MenuHeaderDropdownItem: () => import('./MenuHeaderDropdownItem.vue') as any,
  },

  props: {
    element: { type: Object as PropType<MenuElement>, required: true },
    locale: { type: String, required: true },
    depth: { type: Number, default: 0 },
  },

  data() {
    return {
      isOpen: false as boolean,
    };
  },

  computed: {
    label(): string {
      return this.element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.textLink ?? '';
    },
    url(): string {
      return this.element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.link ?? '#';
    },
    hasChildren(): boolean {
      return !!this.element.children && this.element.children.length > 0;
    },
  },

  methods: {
    open(): void {
      this.isOpen = true;
    },
    close(): void {
      this.isOpen = false;
    },
  },
});
</script>

<template>
  <li v-show="!element.disabled" class="relative" @mouseenter="open" @mouseleave="close">
    <!-- Élément avec enfants -->
    <template v-if="hasChildren">
      <button
        class="w-full flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
        :class="depth > 0 ? 'justify-between w-full' : ''"
        style="color: var(--text-primary)"
      >
        {{ label }}
        <svg
          class="w-3.5 h-3.5 shrink-0"
          :class="depth > 0 ? '' : 'rotate-90'"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Dropdown récursif -->
      <ul
        v-show="isOpen"
        class="absolute z-50 min-w-44 py-1 rounded-xl border shadow-lg"
        :class="depth === 0 ? 'top-full left-0 mt-1' : 'top-0 left-full ml-1'"
        style="background-color: var(--bg-card); border-color: var(--border-color)"
      >
        <MenuHeaderDropdownItem
          v-for="child in element.children"
          :key="child.id"
          :element="child"
          :locale="locale"
          :depth="depth + 1"
        />
      </ul>
    </template>

    <!-- Élément sans enfants -->
    <template v-else>
      <a
        :href="url"
        class="flex items-center px-3 py-2 rounded-lg text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
        style="color: var(--text-primary)"
        @click.prevent
      >
        {{ label }}
      </a>
    </template>
  </li>
</template>

<style scoped></style>

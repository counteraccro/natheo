<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MenuElement } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuLeftRightItem',

  components: {
    MenuLeftRightItem: () => import('./MenuLeftRightItem.vue') as any,
  },

  props: {
    element: {
      type: Object as PropType<MenuElement>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    depth: {
      type: Number,
      default: 0,
    },
    isRight: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      isOpen: false as boolean,
    };
  },

  computed: {
    label(): string {
      const translation = this.element.menuElementTranslations.find(({ locale }) => locale === this.locale);
      return translation?.textLink ?? '';
    },

    url(): string {
      const translation = this.element.menuElementTranslations.find(({ locale }) => locale === this.locale);
      return translation?.link ?? '#';
    },

    hasChildren(): boolean {
      return !!this.element.children && this.element.children.length > 0;
    },

    /**
     * Calcule le padding gauche ou droit en fonction de la profondeur
     */
    paddingStyle(): string {
      const base = 16;
      const indent = this.depth * 16;
      if (this.isRight) {
        return `padding-right: ${base + indent}px; padding-left: ${base}px;`;
      }
      return `padding-left: ${base + indent}px; padding-right: ${base}px;`;
    },

    /**
     * Taille du texte en fonction de la profondeur
     */
    textSize(): string {
      if (this.depth === 0) return 'text-sm font-semibold';
      if (this.depth === 1) return 'text-sm font-normal';
      return 'text-xs font-normal';
    },
  },

  methods: {
    toggle(): void {
      this.isOpen = !this.isOpen;
    },
  },
});
</script>

<template>
  <li v-show="!element.disabled" class="border-b last:border-b-0" style="border-color: var(--border-color)">
    <!-- Élément avec enfants -->
    <template v-if="hasChildren">
      <button
        class="w-full flex items-center justify-between gap-2 py-2.5 transition-colors"
        :class="[textSize, isRight ? 'flex-row-reverse' : '']"
        :style="[paddingStyle, isOpen ? 'color: var(--primary);' : 'color: var(--text-primary);']"
        @click="toggle"
      >
        <span>{{ label }}</span>
        <svg
          class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
          :class="isOpen ? 'rotate-90' : ''"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Sous-menu récursif -->
      <ul v-show="isOpen" class="border-t" style="border-color: var(--border-color); background-color: var(--bg-hover)">
        <MenuLeftRightItem
          v-for="child in element.children"
          :key="child.id"
          :element="child"
          :locale="locale"
          :depth="depth + 1"
          :is-right="isRight"
        />
      </ul>
    </template>

    <!-- Élément sans enfants -->
    <template v-else>
      <a
        :href="url"
        :target="element.linkTarget"
        class="flex items-center py-2.5 transition-colors group"
        :class="[textSize, isRight ? 'flex-row-reverse' : '']"
        :style="[paddingStyle, depth === 0 ? 'color: var(--text-primary);' : 'color: var(--text-secondary);']"
        @click.prevent
      >
        <span
          v-if="depth > 0"
          class="rounded-full shrink-0"
          :class="[isRight ? 'ml-2' : 'mr-2', depth === 1 ? 'w-1.5 h-1.5' : 'w-1 h-1']"
          style="background-color: var(--primary-light)"
        ></span>
        <span class="group-hover:underline" style="text-decoration-color: var(--primary)">
          {{ label }}
        </span>
      </a>
    </template>
  </li>
</template>

<style scoped></style>

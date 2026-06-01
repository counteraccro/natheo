<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MenuElement } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuFooterColumnItem',

  components: {
    MenuFooterColumnItem: () => import('./MenuFooterColumnItem.vue') as any,
  },

  props: {
    element: { type: Object as PropType<MenuElement>, required: true },
    locale: { type: String, required: true },
    depth: { type: Number, default: 0 },
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
});
</script>

<template>
  <li v-show="!element.disabled">
    <template v-if="hasChildren">
      <span
        class="font-medium block mb-1"
        :class="depth === 0 ? 'text-sm' : 'text-xs'"
        :style="depth === 0 ? 'color: var(--text-primary);' : 'color: var(--text-secondary);'"
      >
        {{ label }}
      </span>
      <ul class="space-y-1.5" :class="depth > 0 ? 'ml-3 mt-1' : ''">
        <MenuFooterColumnItem
          v-for="child in element.children"
          :key="child.id"
          :element="child"
          :locale="locale"
          :depth="depth + 1"
        />
      </ul>
    </template>

    <template v-else>
      <div class="flex items-center gap-2" :class="depth > 0 ? 'ml-3' : ''">
        <span
          v-if="depth > 0"
          class="rounded-full flex-shrink-0"
          :class="depth === 1 ? 'w-1.5 h-1.5' : 'w-1 h-1'"
          style="background-color: var(--primary-light)"
        ></span>

        <a
          :href="url"
          class="transition-colors hover:underline"
          :class="depth === 0 ? 'text-sm' : 'text-xs'"
          style="color: var(--text-secondary); text-decoration-color: var(--primary)"
          @click.prevent
        >
          {{ label }}
        </a>
      </div>
    </template>
  </li>
</template>

<style scoped></style>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MenuElement } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuHeaderMegaItem',

  components: {
    MenuHeaderMegaItem: () => import('./MenuHeaderMegaItem.vue') as any,
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
        class="block font-semibold mb-2 pb-2 border-b text-xs uppercase tracking-widest"
        style="color: var(--primary); border-color: var(--border-color)"
      >
        {{ label }}
      </span>
      <ul class="space-y-1" :class="depth > 0 ? 'ml-3 mt-2' : 'mt-2'">
        <MenuHeaderMegaItem
          v-for="child in element.children"
          :key="child.id"
          :element="child"
          :locale="locale"
          :depth="depth + 1"
        />
      </ul>
    </template>

    <template v-else>
      <div
        class="flex items-center gap-2.5 group rounded-lg px-2 py-1.5 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
        :class="depth > 0 ? 'ml-2' : ''"
      >
        <div
          v-if="depth === 0"
          class="w-7 h-7 rounded-md flex items-center justify-center shrink-0"
          style="background-color: var(--primary-lighter)"
        >
          <svg class="w-3.5 h-3.5" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
            />
          </svg>
        </div>

        <span
          v-else
          class="rounded-full shrink-0 transition-colors"
          :class="depth === 1 ? 'w-1.5 h-1.5' : 'w-1 h-1'"
          style="background-color: var(--primary-light)"
        ></span>

        <a
          :href="url"
          class="transition-colors truncate max-w-40"
          :class="depth === 0 ? 'text-sm font-medium' : 'text-xs'"
          style="color: var(--text-secondary)"
          @click.prevent
        >
          {{ label }}
        </a>
      </div>
    </template>
  </li>
</template>

<style scoped></style>

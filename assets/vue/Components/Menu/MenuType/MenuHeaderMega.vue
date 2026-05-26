<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData, MenuElement } from '@/ts/Menu/type';
import MenuHeaderMegaItem from './MenuHeaderMegaItem.vue';

export default defineComponent({
  name: 'MenuHeaderMega',
  components: { MenuHeaderMegaItem },
  props: {
    menu: { type: Object as PropType<Menu>, required: true },
    locale: { type: String, required: true },
    data: { type: Object as PropType<LoadMenuData>, required: true },
    columns: { type: Number, default: 0 }, // 0 = auto, 2, 3, 4
  },

  data() {
    return {
      openItem: null as number | null,
    };
  },

  methods: {
    getLabel(element: MenuElement): string {
      return element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.textLink ?? '';
    },
    getUrl(element: MenuElement): string {
      return element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.link ?? '#';
    },
    hasChildren(element: MenuElement): boolean {
      return !!element.children && element.children.length > 0;
    },
    open(id: number): void {
      this.openItem = id;
    },
    close(): void {
      this.openItem = null;
    },
    gridClass(element: MenuElement): string {
      if (this.columns > 0) return `grid-cols-${this.columns}`;
      const count = element.children?.length ?? 1;
      return `grid-cols-${Math.min(count, 4)}`;
    },
  },
});
</script>

<template>
  <nav
    class="w-full rounded-xl border relative"
    style="background-color: var(--bg-card); border-color: var(--border-color)"
    @mouseleave="close"
  >
    <div class="px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2 font-bold text-sm" style="color: var(--text-primary)">
        <div
          class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
          style="background-color: var(--primary)"
        >
          N
        </div>
        {{ data.name }}
      </div>

      <ul class="hidden md:flex items-center gap-1">
        <li
          v-for="element in menu.menuElements"
          :key="element.id"
          v-show="!element.disabled"
          @mouseenter="hasChildren(element) ? open(element.id) : null"
        >
          <template v-if="hasChildren(element)">
            <button
              class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
              :style="openItem === element.id ? 'color: var(--primary);' : 'color: var(--text-primary);'"
            >
              {{ getLabel(element) }}
              <svg
                class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                :class="openItem === element.id ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </template>

          <template v-else>
            <a
              :href="getUrl(element)"
              class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
              style="color: var(--text-primary)"
              @click.prevent
            >
              {{ getLabel(element) }}
            </a>
          </template>
        </li>
      </ul>

      <button class="md:hidden p-2 rounded-lg" style="color: var(--text-secondary)">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <template v-for="element in menu.menuElements" :key="'panel-' + element.id">
      <div
        v-if="hasChildren(element)"
        v-show="openItem === element.id"
        class="absolute left-0 right-0 top-full z-50 border-x border-b shadow-xl"
        style="
          background-color: var(--bg-card);
          border-color: var(--border-color);
          border-bottom-left-radius: 0.75rem;
          border-bottom-right-radius: 0.75rem;
        "
      >
        <div class="h-px" style="background-color: var(--border-color)"></div>
        <div class="p-6">
          <div class="grid gap-8" :class="gridClass(element)">
            <div v-for="child in element.children" :key="child.id" v-show="!child.disabled">
              <ul class="space-y-2">
                <MenuHeaderMegaItem :element="child" :locale="locale" :depth="0" />
              </ul>
            </div>
          </div>
        </div>
      </div>
    </template>
  </nav>
</template>

<style scoped></style>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData, MenuElement } from '@/ts/Menu/Menu.type';
import MenuFooterColumnItem from './MenuFooterColumnItem.vue';

export default defineComponent({
  name: 'MenuFooterColumn',
  components: { MenuFooterColumnItem },
  props: {
    menu: { type: Object as PropType<Menu>, required: true },
    locale: { type: String, required: true },
    data: { type: Object as PropType<LoadMenuData>, required: true },
  },

  methods: {
    getLabel(element: MenuElement): string {
      return element.menuElementTranslations.find(({ locale }) => locale === this.locale)?.textLink ?? '';
    },
  },
});
</script>

<template>
  <footer class="w-full rounded-xl border bg-card border-line">
    <div class="px-6 py-8">
      <div class="grid gap-8" :class="`grid-cols-1 sm:grid-cols-${Math.min(menu.menuElements.length, 4)}`">
        <div v-for="column in menu.menuElements" :key="column.id" v-show="!column.disabled">
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-fg">
            {{ getLabel(column) }}
          </h3>

          <ul class="space-y-2.5">
            <MenuFooterColumnItem
              v-for="child in column.children"
              :key="child.id"
              :element="child"
              :locale="locale"
              :depth="0"
            />
          </ul>
        </div>
      </div>
    </div>

    <div class="px-6 py-4 border-t flex items-center justify-between border-line">
      <span class="text-xs text-fg-subtle"> © {{ new Date().getFullYear() }} {{ data.name }} </span>
    </div>
  </footer>
</template>

<style scoped></style>

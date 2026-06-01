<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData, MenuElement } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuFooterRight',
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
  <footer class="w-full rounded-xl border" style="background-color: var(--bg-card); border-color: var(--border-color)">
    <div class="px-6 py-4 flex items-center justify-between flex-wrap gap-4">
      <span class="text-sm" style="color: var(--text-secondary)">
        © {{ new Date().getFullYear() }} {{ data.name }}
      </span>
      <ul class="flex items-center gap-6 flex-wrap">
        <li v-for="element in menu.menuElements" :key="element.id" v-show="!element.disabled">
          <a
            :href="getUrl(element)"
            class="text-sm transition-colors hover:underline"
            style="color: var(--text-secondary); text-decoration-color: var(--primary)"
            @click.prevent
          >
            {{ getLabel(element) }}
          </a>
        </li>
      </ul>
    </div>
  </footer>
</template>

<style scoped></style>

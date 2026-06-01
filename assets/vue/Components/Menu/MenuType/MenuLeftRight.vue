<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData } from '@/ts/Menu/type';
import MenuLeftRightItem from './MenuLeftRightItem.vue';

export default defineComponent({
  name: 'MenuLeftRight',
  components: { MenuLeftRightItem },
  props: {
    menu: {
      type: Object as PropType<Menu>,
      required: true,
    },
    type: {
      type: Number as PropType<number>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    data: {
      type: Object as PropType<LoadMenuData>,
      required: true,
    },
  },

  computed: {
    isRight(): boolean {
      return Number(this.menu.position) === 2;
    },
  },
});
</script>

<template>
  <nav
    class="w-full rounded-xl overflow-hidden border"
    style="border-color: var(--border-color); background-color: var(--bg-card)"
  >
    <ul>
      <MenuLeftRightItem
        v-for="element in menu.menuElements"
        :key="element.id"
        :element="element"
        :locale="locale"
        :depth="0"
        :is-right="isRight"
        :type="type"
      />
    </ul>
  </nav>
</template>

<style scoped></style>

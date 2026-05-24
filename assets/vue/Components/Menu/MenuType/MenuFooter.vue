<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData } from '@/ts/Menu/type';
import MenuFooterColumn from './MenuFooterColumn.vue';
import MenuFooterRight from './MenuFooterRight.vue';
import MenuFooterCenter from './MenuFooterCenter.vue';

export default defineComponent({
  name: 'MenuFooter',
  components: { MenuFooterColumn, MenuFooterRight, MenuFooterCenter },
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
});
</script>

<template>
  <MenuFooterColumn v-if="type === 16" :menu="menu" :locale="locale" :data="data" />
  <MenuFooterRight v-else-if="type === 17" :menu="menu" :locale="locale" :data="data" />
  <MenuFooterCenter v-else-if="type === 18" :menu="menu" :locale="locale" :data="data" />
</template>

<style scoped></style>

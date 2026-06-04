<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Menu, LoadMenuData } from '@/ts/Menu/type';
import MenuHeaderNavbar from './MenuHeaderNavbar.vue';
import MenuHeaderDropdown from './MenuHeaderDropdown.vue';
import MenuHeaderMega from './MenuHeaderMega.vue';

export default defineComponent({
  name: 'MenuHeader',
  components: { MenuHeaderNavbar, MenuHeaderDropdown, MenuHeaderMega },
  props: {
    menu: { type: Object as PropType<Menu>, required: true },
    type: { type: Number as PropType<number>, required: true },
    locale: { type: String, required: true },
    data: { type: Object as PropType<LoadMenuData>, required: true },
  },
});
</script>

<template>
  <MenuHeaderNavbar v-if="type === 1" :menu="menu" :locale="locale" :data="data" />
  <MenuHeaderDropdown v-else-if="type === 2" :menu="menu" :locale="locale" :data="data" />
  <MenuHeaderMega v-else-if="type === 3" :columns="0" :menu="menu" :locale="locale" :data="data" />
  <MenuHeaderMega v-else-if="type === 4" :columns="2" :menu="menu" :locale="locale" :data="data" />
  <MenuHeaderMega v-else-if="type === 5" :columns="3" :menu="menu" :locale="locale" :data="data" />
  <MenuHeaderMega v-else-if="type === 6" :columns="4" :menu="menu" :locale="locale" :data="data" />
</template>

<style scoped></style>

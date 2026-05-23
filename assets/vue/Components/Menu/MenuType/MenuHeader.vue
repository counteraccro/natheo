<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { LoadMenuData, Menu } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuHeader',
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

<template>Menu Header</template>

<style scoped></style>

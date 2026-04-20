<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Locales, MenuDatas, Translate, Urls, Menu } from '@/ts/Menu/type';
import axios from 'axios';
import SkeletonRenderMenu from '@/vue/Components/Skeleton/Menu/MenuRender.vue';
import SkeletonFormMenu from '@/vue/Components/Skeleton/Menu/MenuForm.vue';
import SkeletonArchitectureMenu from '@/vue/Components/Skeleton/Menu/MenuArchitecture.vue';

export default defineComponent({
  name: 'Menu',
  components: { SkeletonArchitectureMenu, SkeletonFormMenu, SkeletonRenderMenu },
  props: {
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
    translate: {
      type: Object as PropType<Translate>,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    menu_datas: {
      type: Object as PropType<MenuDatas>,
      required: true,
    },
    id: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      menu: {} as Menu,
      dataMenu: {} as MenuDatas,
    };
  },
  mounted() {
    this.loadMenu();
  },
  methods: {
    loadMenu() {
      let url = this.urls.load_menu + '/' + this.id;
      if (this.id === null) {
        url = this.urls.load_menu;
      }
      this.loading = true;
      axios
        .get(url, {})
        .then((response) => {
          this.menu = response.data.menu;
          this.dataMenu = response.data.data;
          /*this.selectListTypeByPosition(this.menu.position);
          this.renderLabelDisabled();
          this.renderLabelDefaultMenu();

          if (this.menu.id === '') {
            this.canSave = false;
          }

          if (Number.isInteger(idToOpen) && idToOpen > 0) {
            this.updateElement(idToOpen);
          }*/
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <skeleton-render-menu v-if="loading" />
  <skeleton-form-menu v-if="loading" />
  <skeleton-architecture-menu v-if="loading" />
</template>

<style scoped></style>

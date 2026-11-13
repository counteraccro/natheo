<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu du pied de page : un élément racine par colonne, ou une ligne alignée
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import MenuLink from '@/vue/Components/Front/NatheoHorizon/Menu/MenuLink.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf } = useMenus();
const layout = computed(() => layoutOf(props.menu));

const ALIGN: Record<string, string> = { left: 'justify-start', center: 'justify-center', right: 'justify-end' };
const link = 'flex min-h-10 items-center gap-2 text-on-night no-underline hover:underline';
</script>

<template>
  <nav
    v-if="layout.kind === 'columns'"
    :aria-label="translate.menu.footerLabel"
    class="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-8"
  >
    <div v-for="column in menu.elements" :key="column.id">
      <MenuLink :element="column" dot="bg-mod-third" class="mb-1.5 block font-bold text-on-night-muted no-underline hover:underline" />
      <ul v-if="column.children.length">
        <li v-for="child in column.children" :key="child.id">
          <MenuLink :element="child" dot="bg-mod-third" :class="link" />
        </li>
      </ul>
    </div>
  </nav>
  <nav
    v-else
    :aria-label="translate.menu.footerLabel"
    class="flex flex-wrap content-start gap-x-6 gap-y-2"
    :class="ALIGN[layout.align]"
  >
    <MenuLink v-for="element in menu.elements" :key="element.id" :element="element" dot="bg-mod-third" :class="link" />
  </nav>
</template>

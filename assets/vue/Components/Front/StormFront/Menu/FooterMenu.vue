<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu du pied de page : un élément racine par colonne, ou une ligne alignée
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import MenuLink from '@/vue/Components/Front/StormFront/Menu/MenuLink.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf } = useMenus();
const layout = computed(() => layoutOf(props.menu));

const ALIGN: Record<string, string> = { left: 'justify-start', center: 'justify-center', right: 'justify-end' };
const LINK =
  'flex min-h-9.5 items-center gap-1.5 text-fg-muted no-underline hover:text-fg hover:underline aria-[current=page]:text-fg';
</script>

<template>
  <nav
    v-if="layout.kind === 'columns'"
    :aria-label="translate.menu.footerLabel"
    class="grid grid-cols-[repeat(auto-fit,minmax(130px,1fr))] gap-5"
  >
    <ul v-for="column in menu.elements" :key="column.id">
      <li><MenuLink :element="column" :class="LINK" class="font-semibold text-fg" /></li>
      <li v-for="child in column.children" :key="child.id"><MenuLink :element="child" :class="LINK" /></li>
    </ul>
  </nav>
  <nav
    v-else
    :aria-label="translate.menu.footerLabel"
    class="flex flex-wrap content-start gap-x-6 gap-y-1"
    :class="ALIGN[layout.align]"
  >
    <MenuLink v-for="element in menu.elements" :key="element.id" :element="element" :class="LINK" />
  </nav>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu du pied de page du thème Natheo-Base : en colonnes ou sur une ligne
 */
import { computed } from 'vue';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import MenuList from '@/vue/Components/Front/NatheoBase/Menu/MenuList.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { layoutOf, hrefOf } = useMenus();
const layout = computed(() => layoutOf(props.menu));

const ALIGN: Record<string, string> = { left: 'justify-start', center: 'justify-center', right: 'justify-end' };
</script>

<template>
  <nav>
    <div v-if="layout.kind === 'columns'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="column in menu.elements" :key="column.id">
        <a v-if="hrefOf(column)" :href="hrefOf(column) ?? undefined" :target="column.target" class="font-semibold">
          {{ column.label }}
        </a>
        <p v-else class="font-semibold">{{ column.label }}</p>
        <MenuList v-if="column.children.length" :elements="column.children" class="mt-2" />
      </div>
    </div>
    <MenuList v-else :elements="menu.elements" horizontal :class="ALIGN[layout.align]" />
  </nav>
</template>

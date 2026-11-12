<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste d'éléments de menu, sous-menus imbriqués
 */
import { useMenus } from '@/vue/Composables/Front/useMenus';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

defineOptions({ name: 'MenuList' });
defineProps<{ elements: MenuElement[]; horizontal?: boolean }>();
const { hrefOf } = useMenus();
</script>

<template>
  <ul :class="horizontal ? 'flex flex-wrap gap-4' : 'space-y-1 pl-4'">
    <li v-for="element in elements" :key="element.id">
      <a v-if="hrefOf(element)" :href="hrefOf(element) ?? undefined" :target="element.target" class="hover:underline">
        {{ element.label }}
      </a>
      <span v-else class="font-medium">{{ element.label }}</span>
      <MenuList v-if="element.children.length" :elements="element.children" />
    </li>
  </ul>
</template>

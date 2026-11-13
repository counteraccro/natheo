<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste dépliée d'un menu de côté, sous-menus en retrait
 */
import MenuLink from '@/vue/Components/Front/NatheoHorizon/Menu/MenuLink.vue';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

defineOptions({ name: 'SideMenuList' });
defineProps<{ elements: MenuElement[]; nested?: boolean }>();
</script>

<template>
  <ul :class="{ 'ml-2 border-l border-line pl-4': nested }">
    <li v-for="element in elements" :key="element.id">
      <MenuLink
        :element="element"
        class="flex min-h-10 items-center gap-2.5 font-medium text-fg no-underline hover:text-primary aria-[current=page]:font-bold aria-[current=page]:text-primary"
      />
      <SideMenuList v-if="element.children.length" :elements="element.children" nested />
    </li>
  </ul>
</template>

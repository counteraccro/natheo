<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste d'éléments de menu sur autant de niveaux que nécessaire, chaque sous-niveau en retrait
 */
import MenuLink from '@/vue/Components/Front/StormFront/Menu/MenuLink.vue';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

defineOptions({ name: 'NestedMenuList' });
defineProps<{
  elements: MenuElement[];
  /** Classes des liens, à tous les niveaux */
  linkClass: string;
  /** Classes de la liste d'un sous-niveau */
  nestedClass: string;
}>();
</script>

<template>
  <ul>
    <li v-for="element in elements" :key="element.id">
      <MenuLink :element="element" :class="linkClass" />
      <NestedMenuList
        v-if="element.children.length"
        :elements="element.children"
        :link-class="linkClass"
        :nested-class="nestedClass"
        :class="nestedClass"
      />
    </li>
  </ul>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste d'éléments de menu, sous-menus imbriqués, élément de la page affichée signalé
 */
import { useMenus } from '@/vue/Composables/Front/useMenus';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

defineOptions({ name: 'MenuList' });
defineProps<{ elements: MenuElement[]; horizontal?: boolean }>();
const { hrefOf, isActive } = useMenus();
</script>

<template>
  <ul :class="horizontal ? 'flex flex-wrap gap-4' : 'space-y-1'">
    <li v-for="element in elements" :key="element.id">
      <a
        v-if="hrefOf(element)"
        :href="hrefOf(element) ?? undefined"
        :target="element.target"
        :aria-current="isActive(element) && !element.children.length ? 'page' : undefined"
        class="hover:underline aria-[current=page]:font-semibold"
      >
        {{ element.label }}
      </a>
      <span v-else class="font-medium">{{ element.label }}</span>
      <MenuList v-if="element.children.length && !horizontal" :elements="element.children" class="pl-4 mt-1" />
    </li>
  </ul>
</template>

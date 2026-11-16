<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu de côté (gauche ou droite) du thème Natheo-Base : déplié ou en accordéon
 */
import { computed } from 'vue';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import MenuList from '@/vue/Components/Front/NatheoBase/Menu/MenuList.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { layoutOf, hrefOf, isActive } = useMenus();
const accordion = computed(() => layoutOf(props.menu).kind === 'accordion');
</script>

<template>
  <nav class="text-sm">
    <ul v-if="accordion" class="space-y-2">
      <li v-for="element in menu.elements" :key="element.id">
        <details v-if="element.children.length" :open="isActive(element)">
          <summary class="cursor-pointer font-medium">{{ element.label }}</summary>
          <MenuList :elements="element.children" class="pl-4 mt-1" />
        </details>
        <a
          v-else-if="hrefOf(element)"
          :href="hrefOf(element) ?? undefined"
          :target="element.target"
          class="hover:underline"
        >
          {{ element.label }}
        </a>
        <span v-else class="font-medium">{{ element.label }}</span>
      </li>
    </ul>
    <MenuList v-else :elements="menu.elements" />
  </nav>
</template>

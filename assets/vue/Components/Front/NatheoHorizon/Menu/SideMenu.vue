<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Menu de côté (gauche ou droite) : déplié ou en accordéon
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import MenuLink from '@/vue/Components/Front/NatheoHorizon/Menu/MenuLink.vue';
import NestedMenuList from '@/vue/Components/Front/NatheoHorizon/Menu/NestedMenuList.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf, isActive } = useMenus();
const accordion = computed(() => layoutOf(props.menu).kind === 'accordion');
const LINK =
  'flex min-h-10 items-center gap-2.5 font-medium text-fg no-underline hover:text-primary aria-[current=page]:font-bold aria-[current=page]:text-primary';
const NESTED = 'ml-2 border-l border-line pl-4';
</script>

<template>
  <nav :aria-label="translate.menu.sideLabel" class="order-2 lg:sticky lg:top-25 lg:order-none">
    <ul v-if="accordion">
      <li v-for="element in menu.elements" :key="element.id">
        <details v-if="element.children.length" :open="isActive(element)" class="group">
          <summary
            class="flex min-h-10 cursor-pointer list-none items-center justify-between gap-2.5 font-semibold [&::-webkit-details-marker]:hidden"
          >
            {{ element.label }}
            <HorizonIcon name="chevron-down" class="size-4 transition-transform group-open:rotate-180" />
          </summary>
          <NestedMenuList :elements="element.children" :link-class="LINK" :nested-class="NESTED" :class="NESTED" />
        </details>
        <MenuLink v-else :element="element" :class="LINK" />
      </li>
    </ul>
    <NestedMenuList v-else :elements="menu.elements" :link-class="LINK" :nested-class="NESTED" />
  </nav>
</template>

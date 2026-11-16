<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu de côté de la page (gauche ou droite) : déplié ou en accordéon
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import FrontIcon from '@/vue/Components/Front/Global/FrontIcon.vue';
import MenuLink from '@/vue/Components/Front/StormFront/Menu/MenuLink.vue';
import NestedMenuList from '@/vue/Components/Front/StormFront/Menu/NestedMenuList.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf, isActive } = useMenus();
const accordion = computed(() => layoutOf(props.menu).kind === 'accordion');
const LINK =
  'flex min-h-10 items-center gap-2 font-medium text-fg no-underline hover:text-primary aria-[current=page]:font-semibold aria-[current=page]:text-primary';
const NESTED = 'pb-2 pl-3 [&_a]:min-h-9 [&_a]:text-fg-muted';
</script>

<template>
  <nav :aria-label="translate.menu.sideLabel" class="order-2 text-[0.95rem] xl:sticky xl:top-8 xl:order-none">
    <ul class="divide-y divide-line">
      <li v-for="element in menu.elements" :key="element.id">
        <details v-if="accordion && element.children.length" :open="isActive(element)" class="group">
          <summary
            class="flex min-h-10 cursor-pointer list-none items-center justify-between gap-2 font-semibold [&::-webkit-details-marker]:hidden"
          >
            {{ element.label }}
            <FrontIcon name="chevron-down" class="size-4 transition-transform group-open:rotate-180" />
          </summary>
          <NestedMenuList :elements="element.children" :link-class="LINK" :nested-class="NESTED" :class="NESTED" />
        </details>
        <template v-else>
          <MenuLink :element="element" :class="LINK" />
          <NestedMenuList
            v-if="element.children.length"
            :elements="element.children"
            :link-class="LINK"
            :nested-class="NESTED"
            :class="NESTED"
          />
        </template>
      </li>
    </ul>
  </nav>
</template>

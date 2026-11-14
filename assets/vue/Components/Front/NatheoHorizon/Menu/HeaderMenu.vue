<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Menu de l'en-tête sur grand écran : sous-menus déroulants ou méga menu en colonnes
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import NavPanel from '@/vue/Components/Front/NatheoHorizon/Layout/NavPanel.vue';
import MenuLink from '@/vue/Components/Front/NatheoHorizon/Menu/MenuLink.vue';
import NestedMenuList from '@/vue/Components/Front/NatheoHorizon/Menu/NestedMenuList.vue';
import type { Menu, MenuElement } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf, isActive } = useMenus();
const layout = computed(() => layoutOf(props.menu));

function megaColumns(element: MenuElement): string {
  return `repeat(${Math.min(layout.value.columns, element.children.length)}, minmax(190px, 1fr))`;
}
</script>

<template>
  <nav :aria-label="translate.menu.headerLabel" class="ml-2 hidden lg:block">
    <ul class="flex gap-1">
      <li v-for="element in menu.elements" :key="element.id">
        <MenuLink
          v-if="!element.children.length"
          :element="element"
          dot="bg-mod-third"
          class="inline-flex min-h-11 items-center gap-1.5 rounded-nh px-3 font-medium text-on-night-muted no-underline hover:bg-white/10 aria-[current=page]:text-on-night"
        />
        <NavPanel v-else :label="element.label" :current="isActive(element)">
          <div v-if="layout.kind === 'mega'" class="grid gap-6 p-5" :style="{ gridTemplateColumns: megaColumns(element) }">
            <div v-for="child in element.children" :key="child.id">
              <MenuLink :element="child" class="mb-1 flex items-center gap-2 text-[0.95rem] font-bold text-fg no-underline" />
              <NestedMenuList
                v-if="child.children.length"
                :elements="child.children"
                link-class="flex min-h-10 items-center gap-2 font-medium text-fg-muted no-underline hover:text-primary"
                nested-class="ml-1 border-l border-line pl-3"
              />
            </div>
          </div>
          <NestedMenuList
            v-else
            :elements="element.children"
            link-class="flex min-h-11 items-center gap-2.5 rounded-nh px-3 text-fg no-underline hover:bg-surface"
            nested-class="mb-1 ml-4 border-l border-line pl-1"
            class="min-w-57 p-1.5"
          />
        </NavPanel>
      </li>
    </ul>
  </nav>
</template>

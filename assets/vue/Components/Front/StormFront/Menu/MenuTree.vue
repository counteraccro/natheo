<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu d'en-tête dans le volet ouvert depuis le rail : entrées dépliables, colonnes pour un méga menu
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import MegaColumns from '@/vue/Components/Front/StormFront/Menu/MegaColumns.vue';
import MenuLink from '@/vue/Components/Front/StormFront/Menu/MenuLink.vue';
import NestedMenuList from '@/vue/Components/Front/StormFront/Menu/NestedMenuList.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf, isActive } = useMenus();
const layout = computed(() => layoutOf(props.menu));

const ENTRY =
  'flex min-h-13 w-full cursor-pointer items-center justify-between gap-2.5 text-[1.3rem] font-medium tracking-[-0.02em] text-on-rail no-underline';
const SUB =
  'flex min-h-10.5 items-center gap-2 text-on-rail-muted no-underline hover:text-on-rail aria-[current=page]:text-on-rail aria-[current=page]:shadow-[inset_3px_0_0_var(--bolt)] aria-[current=page]:pl-3';
</script>

<template>
  <nav :aria-label="translate.menu.headerLabel">
    <ul>
      <li v-for="element in menu.elements" :key="element.id" class="border-b border-rail-line">
        <MenuLink
          v-if="!element.children.length"
          :element="element"
          :class="ENTRY"
          class="hover:underline aria-[current=page]:shadow-[inset_3px_0_0_var(--bolt)] aria-[current=page]:pl-3"
        />
        <details v-else :open="isActive(element)" class="group">
          <summary :class="ENTRY" class="list-none [&::-webkit-details-marker]:hidden">
            {{ element.label }}
            <UiIcon name="chevron-down" class="size-5 transition-transform group-open:rotate-180" />
          </summary>
          <MegaColumns v-if="layout.kind === 'mega'" :element="element" :columns="layout.columns" class="pb-4" />
          <NestedMenuList v-else :elements="element.children" :link-class="SUB" nested-class="pl-4" class="pb-3 pl-4" />
        </details>
      </li>
    </ul>
  </nav>
</template>

<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu d'en-tête dans la barre latérale : entrées dépliables sur place, ou volet de colonnes pour un méga menu
 */
import { computed } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import MenuLink from '@/vue/Components/Front/StormFront/Menu/MenuLink.vue';
import NestedMenuList from '@/vue/Components/Front/StormFront/Menu/NestedMenuList.vue';
import type { Menu, MenuElement } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu; sheet: string | null; sheetId: string }>();
const emit = defineEmits<{ mega: [element: MenuElement, opener: HTMLElement] }>();
const { translate } = useFrontContext();
const { layoutOf, isActive } = useMenus();
const mega = computed(() => layoutOf(props.menu).kind === 'mega');

const ENTRY =
  'flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 px-5 text-left text-base font-medium text-on-rail-muted no-underline hover:bg-white/5 hover:text-on-rail';
const LINK = `${ENTRY} aria-[current=page]:text-on-rail aria-[current=page]:shadow-[inset_3px_0_0_var(--bolt)]`;
const SUB =
  'flex min-h-9.5 items-center gap-2 pr-5 pl-8 text-[0.94rem] text-on-rail-muted no-underline hover:bg-white/5 hover:text-on-rail aria-[current=page]:text-on-rail aria-[current=page]:shadow-[inset_3px_0_0_var(--bolt)]';
</script>

<template>
  <nav :aria-label="translate.menu.headerLabel">
    <ul>
      <li v-for="element in menu.elements" :key="element.id">
        <MenuLink v-if="!element.children.length" :element="element" :class="LINK" />
        <button
          v-else-if="mega"
          type="button"
          :class="[ENTRY, { 'text-on-rail': isActive(element) }]"
          class="aria-expanded:bg-white/7 aria-expanded:text-on-rail"
          :aria-expanded="sheet === `mega-${element.id}`"
          :aria-controls="sheetId"
          @click="emit('mega', element, $event.currentTarget as HTMLElement)"
        >
          {{ element.label }}
          <UiIcon name="chevron-down" class="size-4 -rotate-90" />
        </button>
        <details v-else :open="isActive(element)" class="group">
          <summary :class="[ENTRY, { 'text-on-rail': isActive(element) }]" class="list-none [&::-webkit-details-marker]:hidden">
            {{ element.label }}
            <UiIcon name="chevron-down" class="size-4 transition-transform group-open:rotate-180" />
          </summary>
          <NestedMenuList :elements="element.children" :link-class="SUB" nested-class="pl-3" class="pb-2" />
        </details>
      </li>
    </ul>
  </nav>
</template>

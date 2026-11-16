<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Colonnes d'une entrée de méga menu : un enfant par colonne, titre puis ses liens
 */
import { computed } from 'vue';
import MenuLink from '@/vue/Components/Front/StormFront/Menu/MenuLink.vue';
import NestedMenuList from '@/vue/Components/Front/StormFront/Menu/NestedMenuList.vue';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ element: MenuElement; columns: number }>();
const template = computed(
  () => `repeat(${Math.min(props.columns, props.element.children.length)}, minmax(0, 1fr))`
);
</script>

<template>
  <div class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-(--sf-cols)" :style="{ '--sf-cols': template }">
    <div v-for="child in element.children" :key="child.id" class="min-w-0">
      <MenuLink
        :element="child"
        class="flex min-h-10 items-center gap-2 text-[1.1rem] font-semibold text-on-rail no-underline hover:underline aria-[current=page]:shadow-[inset_3px_0_0_var(--bolt)] aria-[current=page]:pl-3"
      />
      <NestedMenuList
        v-if="child.children.length"
        :elements="child.children"
        link-class="flex min-h-10 items-center gap-2 text-on-rail-muted no-underline hover:text-on-rail aria-[current=page]:text-on-rail aria-[current=page]:shadow-[inset_3px_0_0_var(--bolt)] aria-[current=page]:pl-3"
        nested-class="pl-4"
      />
    </div>
  </div>
</template>

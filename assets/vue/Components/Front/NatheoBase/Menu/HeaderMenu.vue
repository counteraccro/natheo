<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu de l'en-tête du thème Natheo-Base : déroulant, grand panneau en colonnes ou panneau latéral
 */
import { computed, ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import MenuList from '@/vue/Components/Front/NatheoBase/Menu/MenuList.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ menu: Menu }>();
const { translate } = useFrontContext();
const { layoutOf, hrefOf, isActive } = useMenus();
const layout = computed(() => layoutOf(props.menu));
const open = ref(false);

const MEGA_COLUMNS: Record<number, string> = {
  1: 'grid-cols-1',
  2: 'sm:grid-cols-2',
  3: 'sm:grid-cols-3',
  4: 'sm:grid-cols-2 lg:grid-cols-4',
};
</script>

<template>
  <div>
    <button
      type="button"
      class="border px-2 py-1"
      :class="layout.kind === 'sidebar' ? '' : 'md:hidden'"
      :aria-expanded="open"
      @click="open = !open"
    >
      {{ translate.header.openMenu }}
    </button>

    <aside
      v-if="layout.kind === 'sidebar'"
      v-show="open"
      class="fixed inset-y-0 left-0 z-20 w-72 overflow-y-auto bg-white dark:bg-gray-900 border-r p-4"
    >
      <button type="button" class="mb-4 underline" @click="open = false">×</button>
      <MenuList :elements="menu.elements" />
    </aside>

    <ul v-else :class="open ? 'flex' : 'hidden md:flex'" class="flex-col md:flex-row gap-4 mt-2 md:mt-0">
      <li v-for="element in menu.elements" :key="element.id" class="relative">
        <a
          v-if="!element.children.length && hrefOf(element)"
          :href="hrefOf(element) ?? undefined"
          :target="element.target"
          :aria-current="isActive(element) ? 'page' : undefined"
          class="hover:underline aria-[current=page]:font-semibold"
        >
          {{ element.label }}
        </a>
        <details v-else-if="element.children.length" class="group">
          <summary class="cursor-pointer" :class="{ 'font-semibold': isActive(element) }">{{ element.label }}</summary>
          <div
            class="md:absolute md:z-10 md:mt-2 bg-white dark:bg-gray-900 border p-3"
            :class="layout.kind === 'mega' ? 'md:min-w-[32rem]' : 'md:min-w-48'"
          >
            <div v-if="layout.kind === 'mega'" class="grid gap-4" :class="MEGA_COLUMNS[layout.columns]">
              <div v-for="child in element.children" :key="child.id">
                <a v-if="hrefOf(child)" :href="hrefOf(child) ?? undefined" :target="child.target" class="font-medium">
                  {{ child.label }}
                </a>
                <span v-else class="font-medium">{{ child.label }}</span>
                <MenuList v-if="child.children.length" :elements="child.children" class="mt-1" />
              </div>
            </div>
            <MenuList v-else :elements="element.children" />
          </div>
        </details>
        <span v-else>{{ element.label }}</span>
      </li>
    </ul>
  </div>
</template>

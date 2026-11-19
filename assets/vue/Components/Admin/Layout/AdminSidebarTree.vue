<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Niveau de la sidebar : liens et dossiers repliables (aria-expanded), retrait par niveau
 */
import { useId } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';

defineProps<{ items: SidebarItem[]; openIds: Set<string>; depth: number }>();
const emit = defineEmits<{ toggle: [id: string]; navigate: [item: SidebarItem] }>();
defineSlots<{ link?: (props: { item: SidebarItem; class: string; onClick: () => void }) => unknown }>();

const uid = useId();

const ENTRY =
  'relative flex min-h-9 w-full cursor-pointer items-center gap-2.5 rounded-sm border-0 bg-transparent px-2.5 py-[7px] text-left text-inherit no-underline transition-colors duration-100 hover:bg-side-hover hover:text-on-side focus-visible:outline-primary-on-dark max-md:min-h-11';
const ACTIVE =
  "aria-[current=page]:bg-side-active aria-[current=page]:font-semibold aria-[current=page]:text-on-side aria-[current=page]:before:absolute aria-[current=page]:before:inset-y-2 aria-[current=page]:before:-left-[11px] aria-[current=page]:before:w-[3px] aria-[current=page]:before:rounded-sm aria-[current=page]:before:bg-primary-on-dark aria-[current=page]:before:content-['']";
</script>

<template>
  <ul class="m-0 list-none p-0">
    <li v-for="item in items" :key="item.id">
      <template v-if="item.href">
        <slot name="link" :item="item" :class="`${ENTRY} ${ACTIVE}`" :on-click="() => emit('navigate', item)">
          <a
            :href="item.href"
            :class="[ENTRY, ACTIVE]"
            :aria-current="item.active ? 'page' : undefined"
            @click="emit('navigate', item)"
          >
            <UiIcon v-if="item.icon" :path="item.icon" class="size-5 opacity-85" />
            <span class="min-w-0 flex-1 truncate">{{ item.label }}</span>
            <span
              v-if="item.badge"
              class="grid h-5 min-w-5 place-items-center rounded-full bg-primary-on-dark px-1.5 text-2xs font-bold text-on-side-accent"
            >
              {{ item.badge }}
            </span>
          </a>
        </slot>
      </template>
      <template v-else-if="item.children?.length">
        <button
          type="button"
          :class="ENTRY"
          :aria-expanded="openIds.has(item.id)"
          :aria-controls="`${uid}-${item.id}`"
          class="aria-expanded:text-on-side"
          @click="emit('toggle', item.id)"
        >
          <UiIcon v-if="item.icon" :path="item.icon" class="size-5 opacity-85" />
          <span class="min-w-0 flex-1 truncate">{{ item.label }}</span>
          <span
            v-if="item.badge"
            class="grid h-5 min-w-5 place-items-center rounded-full bg-primary-on-dark px-1.5 text-2xs font-bold text-on-side-accent"
          >
            {{ item.badge }}
          </span>
          <UiIcon
            name="chevron-down"
            class="size-4 opacity-60 transition-transform duration-200"
            :class="openIds.has(item.id) && 'rotate-180'"
          />
        </button>
        <div
          :id="`${uid}-${item.id}`"
          class="grid transition-[grid-template-rows] duration-200 ease-out-soft"
          :class="openIds.has(item.id) ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
          :inert="!openIds.has(item.id) || undefined"
        >
          <div
            class="overflow-hidden"
            :class="
              depth === 0 ? 'ml-[19px] border-l border-side-line pl-2.5' : 'ml-2 border-l border-side-line pl-2.5'
            "
          >
            <AdminSidebarTree
              :items="item.children"
              :open-ids="openIds"
              :depth="depth + 1"
              @toggle="emit('toggle', $event)"
              @navigate="emit('navigate', $event)"
            >
              <template v-if="$slots.link" #link="props"><slot name="link" v-bind="props" /></template>
            </AdminSidebarTree>
          </div>
        </div>
      </template>
    </li>
  </ul>
</template>

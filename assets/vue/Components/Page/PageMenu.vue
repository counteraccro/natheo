<script lang="ts">
/**
 * Gestionnaire des Pages - Onglet Menu
 * @author Gourdon Aymeric
 * @version 2.0
 */

import { defineComponent, PropType } from 'vue';
import {
  Page,
  PageMenus,
  PageMenuItem,
  PageTranslations,
  MENU_POSITION_HEADER,
  MENU_POSITION_RIGHT,
  MENU_POSITION_FOOTER,
  MENU_POSITION_LEFT,
} from '@/ts/Page/type';

export default defineComponent({
  name: 'PageMenu',
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    availableMenus: {
      type: Object as PropType<PageMenus>,
      required: true,
    },
  },
  emits: ['update-menus'],
  computed: {
    menusByPosition(): Record<number, PageMenuItem[]> {
      const result: Record<number, PageMenuItem[]> = {
        [MENU_POSITION_HEADER]: [],
        [MENU_POSITION_RIGHT]: [],
        [MENU_POSITION_FOOTER]: [],
        [MENU_POSITION_LEFT]: [],
      };

      for (const menu of Object.values(this.availableMenus)) {
        if (result[menu.position]) {
          result[menu.position].push(menu);
        }
      }

      return result;
    },
    selectedHeader: {
      get(): number {
        return this.getSelectedForPosition(MENU_POSITION_HEADER);
      },
      set(id: number) {
        this.updateMenuForPosition(MENU_POSITION_HEADER, id);
      },
    },
    selectedRight: {
      get(): number {
        return this.getSelectedForPosition(MENU_POSITION_RIGHT);
      },
      set(id: number) {
        this.updateMenuForPosition(MENU_POSITION_RIGHT, id);
      },
    },
    selectedFooter: {
      get(): number {
        return this.getSelectedForPosition(MENU_POSITION_FOOTER);
      },
      set(id: number) {
        this.updateMenuForPosition(MENU_POSITION_FOOTER, id);
      },
    },
    selectedLeft: {
      get(): number {
        return this.getSelectedForPosition(MENU_POSITION_LEFT);
      },
      set(id: number) {
        this.updateMenuForPosition(MENU_POSITION_LEFT, id);
      },
    },
  },
  methods: {
    getSelectedForPosition(position: number): number {
      const menusForPosition = this.menusByPosition[position] ?? [];
      const selected = menusForPosition.find((m) => this.page.menus.includes(m.id));
      return selected?.id ?? 0;
    },
    updateMenuForPosition(position: number, selectedId: number) {
      const menusForPosition = this.menusByPosition[position] ?? [];
      const idsForPosition = menusForPosition.map((m) => m.id);
      const currentMenus = this.page.menus.filter((id) => !idsForPosition.includes(Number(id)));

      if (selectedId !== 0) {
        this.$emit('update-menus', [...currentMenus.map(Number), selectedId]);
      } else {
        this.$emit('update-menus', currentMenus.map(Number));
      }
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
              d="M9 5v14m8-7h-2m0 0h-2m2 0v2m0-2v-2M3 11h6m-6 4h6m11 4H4c-.55228 0-1-.4477-1-1V6c0-.55228.44772-1 1-1h16c.5523 0 1 .44772 1 1v12c0 .5523-.4477 1-1 1Z"
            />
          </svg>
          {{ translate.page_menu.list_menu_label }}
        </div>
        <p class="card-subtitle">{{ translate.page_menu.list_menu_sub_label }}</p>
      </div>
    </div>

    <div class="p-5 flex flex-col gap-3">
      <div class="card rounded-lg p-4" style="border-color: var(--border-color)">
        <div class="flex items-center gap-2 mb-3">
          <svg class="icon-sm" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
          </svg>
          <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
            translate.page_menu.list_menu_header
          }}</span>
        </div>
        <select class="form-input" v-model.number="selectedHeader">
          <option :value="0">{{ translate.page_menu.list_menu_empty }}</option>
          <option v-for="menu in menusByPosition[1]" :key="menu.id" :value="menu.id" :disabled="menu.disabled">
            {{ menu.name }}{{ menu.disabled ? ' (' + translate.page_menu.list_menu_disabled + ')' : '' }}
          </option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="card rounded-lg p-4" style="border-color: var(--border-color)">
          <div class="flex items-center gap-2 mb-3">
            <svg class="icon-sm" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
              translate.page_menu.list_menu_left
            }}</span>
          </div>
          <select class="form-input" v-model.number="selectedLeft">
            <option :value="0">{{ translate.page_menu.list_menu_empty }}</option>
            <option v-for="menu in menusByPosition[4]" :key="menu.id" :value="menu.id" :disabled="menu.disabled">
              {{ menu.name }}{{ menu.disabled ? ' (' + translate.page_menu.list_menu_disabled + ')' : '' }}
            </option>
          </select>
        </div>

        <div class="card rounded-lg p-4" style="border-color: var(--border-color)">
          <div class="flex items-center gap-2 mb-3">
            <svg class="icon-sm" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
              translate.page_menu.list_menu_right
            }}</span>
          </div>
          <select class="form-input" v-model.number="selectedRight">
            <option :value="0">{{ translate.page_menu.list_menu_empty }}</option>
            <option v-for="menu in menusByPosition[2]" :key="menu.id" :value="menu.id" :disabled="menu.disabled">
              {{ menu.name }}{{ menu.disabled ? ' (' + translate.page_menu.list_menu_disabled + ')' : '' }}
            </option>
          </select>
        </div>
      </div>

      <div class="card rounded-lg p-4" style="border-color: var(--border-color)">
        <div class="flex items-center gap-2 mb-3">
          <svg class="icon-sm" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
          <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
            translate.page_menu.list_menu_footer
          }}</span>
        </div>
        <select class="form-input" v-model.number="selectedFooter">
          <option :value="0">{{ translate.page_menu.list_menu_empty }}</option>
          <option v-for="menu in menusByPosition[3]" :key="menu.id" :value="menu.id" :disabled="menu.disabled">
            {{ menu.name }}{{ menu.disabled ? ' (' + translate.page_menu.list_menu_disabled + ')' : '' }}
          </option>
        </select>
      </div>

      <div class="flex items-start gap-2 p-3 rounded-lg" style="background-color: var(--primary-lighter)">
        <svg
          class="icon-sm shrink-0 mt-0.5"
          style="color: var(--primary)"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <span class="text-xs" style="color: var(--primary)">{{ translate.page_menu.list_menu_help }}</span>
      </div>
    </div>
  </div>
</template>

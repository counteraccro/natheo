<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Locales, MenuDatas, Translate, Urls, Menu, MenuElement } from '@/ts/Menu/type';
import axios from 'axios';
import SkeletonRenderMenu from '@/vue/Components/Skeleton/Menu/MenuRender.vue';
import SkeletonFormMenu from '@/vue/Components/Skeleton/Menu/MenuForm.vue';
import SkeletonArchitectureMenu from '@/vue/Components/Skeleton/Menu/MenuArchitecture.vue';
import MenuTree from '@/vue/Components/Menu/MenuTree.vue';
import Sortable from 'sortablejs';

export default defineComponent({
  name: 'Menu',
  components: { MenuTree, SkeletonArchitectureMenu, SkeletonFormMenu, SkeletonRenderMenu },
  props: {
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
    translate: {
      type: Object as PropType<Translate>,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    menu_datas: {
      type: Object as PropType<MenuDatas>,
      required: true,
    },
    id: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      menu: {} as Menu,
      dataMenu: {} as MenuDatas,
      currentLocale: '',
      listTypeByPosition: {} as Record<string, string>,
      updateNoSave: false,
    };
  },
  mounted() {
    this.loadMenu();
    this.currentLocale = this.locales.current;
  },
  methods: {
    loadMenu() {
      let url = this.urls.load_menu + '/' + this.id;
      if (this.id === null) {
        url = this.urls.load_menu;
      }
      this.loading = true;
      axios
        .get(url, {})
        .then((response) => {
          this.menu = response.data.menu;
          this.dataMenu = response.data.data;
          this.selectListTypeByPosition(this.menu.position);

          /*if (this.menu.id === '') {
            this.canSave = false;
          }

          if (Number.isInteger(idToOpen) && idToOpen > 0) {
            this.updateElement(idToOpen);
          }*/
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.loadDraggable();
        });
    },

    /**
     * Sélectionne la liste de type en fonction de la position
     * @param position
     */
    selectListTypeByPosition(position: string | number): void {
      let isFirstLoad = false;
      if (Object.keys(this.listTypeByPosition).length === 0) {
        isFirstLoad = true;
      }

      this.listTypeByPosition = {};

      for (const key in this.menu_datas.list_type) {
        if (!Object.prototype.hasOwnProperty.call(this.menu_datas.list_position, key)) continue;
        if (key === position.toString()) {
          this.listTypeByPosition = this.menu_datas.list_type[key];
          break;
        }
      }

      if (!isFirstLoad && !(this.menu.type in this.listTypeByPosition)) {
        const first = Object.entries(this.listTypeByPosition)[0];
        this.menu.type = Number(first[0]);
      }
    },

    /**
     * Chargement du dragandDrop
     */
    loadDraggable(): void {
      this.$nextTick(() => {
        const container = this.$refs.rootListRef as HTMLElement;
        if (!container) return;

        Sortable.create(container, {
          handle: '.drag-handle',
          animation: 200,
          ghostClass: 'opacity-40',
          chosenClass: 'ring-2',
          onEnd: ({ oldIndex, newIndex, item, from }) => {
            if (oldIndex === undefined || newIndex === undefined) return;
            if (oldIndex === newIndex) return;

            from.insertBefore(item, from.children[oldIndex] ?? null);

            this.onReorder({ parentId: null, oldIndex, newIndex });
          },
        });
      });
    },

    /**
     * Réordonne les menus
     * @param payload
     */
    onReorder(payload: { parentId: number | null; oldIndex: number; newIndex: number }): void {
      // Niveau racine si parentId est null
      const siblings =
        payload.parentId === null
          ? this.menu.menuElements
          : this.findChildren(this.menu.menuElements, payload.parentId);

      if (!siblings) return;

      const [moved] = siblings.splice(payload.oldIndex, 1);
      siblings.splice(payload.newIndex, 0, moved);

      siblings.forEach((el, index) => {
        el.columnPosition = index + 1;
      });

      this.updateNoSave = true;
    },

    /**
     * Trouve récursivement le tableau children d'un élément par son id
     */
    findChildren(elements: MenuElement[], parentId: number): MenuElement[] | null {
      for (const el of elements) {
        if (el.id === parentId) return el.children ?? null;
        if (el.children) {
          const found = this.findChildren(el.children, parentId);
          if (found) return found;
        }
      }
      return null;
    },

    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event): void {
      this.currentLocale = event.target.value;
    },
  },
});
</script>

<template>
  <skeleton-render-menu v-if="loading" />
  <skeleton-form-menu v-if="loading" />
  <skeleton-architecture-menu v-if="loading" />

  <div class="card rounded-lg overflow-hidden mb-5">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
      <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"
        ></path>
      </svg>
      <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.title_demo }}</span>
    </div>
    <div class="p-4">-- Rendu demo --</div>
  </div>

  <div class="card rounded-lg overflow-hidden mb-5">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
      <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
        ></path>
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
        ></path>
      </svg>
      <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.title_global_form }}</span>
      <div class="ml-auto flex items-center gap-3">
        <div class="input-addon-group">
          <span class="input-addon input-addon-left"
            ><svg
              class="icon-sm"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="m13 19 3.5-9 3.5 9m-6.125-2h5.25M3 7h7m0 0h2m-2 0c0 1.63-.793 3.926-2.239 5.655M7.5 6.818V5m.261 7.655C6.79 13.82 5.521 14.725 4 15m3.761-2.345L5 10m2.761 2.655L10.2 15"
              ></path></svg></span
          ><select id="select-language" class="form-input form-input-sm" style="width: 120px" v-model="currentLocale">
            <option value="" selected>{{ translate.select_locale }}</option>
            <option v-for="(language, key) in locales.localesTranslate" :value="key">
              {{ language }}
            </option>
          </select>
        </div>
        <button class="btn btn-sm btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
            ></path>
          </svg>
          {{ translate.btn_save }}
        </button>
      </div>
    </div>
    <div class="p-4">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-5">
        <div class="space-y-4">
          <div class="form-group">
            <label for="menu-title" class="form-label">{{ translate.input_name_label }}</label>
            <input
              type="text"
              class="form-input"
              id="menu-title"
              v-model="menu.name"
              :placeholder="translate.input_name_placeholder"
            />
          </div>

          <div>
            <div class="form-switch form-switch-inline">
              <input
                class="switch-input no-control event-input"
                type="checkbox"
                role="switch"
                id="default_menu"
                v-model="menu.defaultMenu"
              />
              <label class="switch-toggle" for="default_menu"></label>
              <label class="swith-label" for="default_menu"
                ><span class="switch-label-text"> {{ translate.checkbox_default_menu_true_label }} </span></label
              >
            </div>
            <span
              class="form-text"
              v-html="
                menu.defaultMenu
                  ? translate.checkbox_default_menu_false_label_msg
                  : translate.checkbox_default_menu_true_label_msg
              "
            ></span>
          </div>

          <div>
            <div class="form-switch form-switch-inline">
              <input
                class="switch-input no-control event-input"
                type="checkbox"
                role="switch"
                id="disabled_menu"
                v-model="menu.disabled"
              />
              <label class="switch-toggle" for="disabled_menu"></label>
              <label class="swith-label" for="disabled_menu"
                ><span class="switch-label-text"> {{ translate.checkbox_disabled_label }} </span></label
              >
            </div>
            <span
              class="form-text"
              v-html="menu.disabled ? translate.checkbox_disabled_label_msg : translate.checkbox_enabled_label_msg"
            ></span>
          </div>
        </div>
        <div class="space-y-4">
          <div class="form-group">
            <label class="form-label" for="menu-position">{{ translate.select_position_label }}</label>
            <select
              id="menu-position"
              class="form-input"
              v-model="menu.position"
              @change="selectListTypeByPosition(menu.position)"
            >
              <option v-for="(position, key) in menu_datas.list_position" :value="key">
                {{ position }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="menu-title">{{ translate.select_type_label }}</label>
            <select id="menu-type" class="form-input" v-model="menu.type">
              <option v-for="(position, key) in listTypeByPosition" :value="key">{{ position }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
    <div class="card rounded-lg overflow-hidden xl:col-span-2">
      <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
        <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.title_architecture }}</span>
      </div>

      <div class="p-3 tree-scroll" ref="rootListRef">
        <menu-tree
          v-for="menuElement in menu.menuElements"
          :menu-element="menuElement"
          :translate="translate.menu_tree"
          :locale="currentLocale"
          :id-selected="0"
          :deep="0"
          @reorder="onReorder"
        />
      </div>
      <div class="p-3">
        <div class="mt-3">
          <button class="btn-add-root">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ translate.btn_new_menu_element }}
          </button>
        </div>
      </div>
    </div>
    <div class="card rounded-lg overflow-hidden xl:col-span-3">
      <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color: var(--border-color)">
        <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
          ></path>
        </svg>
        <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
          translate.no_select_menu_form
        }}</span>
      </div>

      <div class="edition-empty">
        <svg class="edition-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"
          ></path>
        </svg>
        <p class="font-semibold" style="color: var(--text-secondary)">{{ translate.no_select_menu_form_msg }}</p>
        <p class="text-sm mt-1" style="color: var(--text-light)">{{ translate.no_select_menu_form_msg_2 }}</p>
        <div class="help-list">
          <div class="help-item">
            <span class="help-icon" style="background-color: #d1fae5; color: #059669">+</span>
            {{ translate.no_select_menu_form_msg_3 }}
          </div>
          <div class="help-item">
            <span class="help-icon" style="background-color: var(--primary-lighter); color: var(--primary)">✎</span>
            {{ translate.no_select_menu_form_msg_4 }}
          </div>
          <div class="help-item">
            <span class="help-icon" style="background-color: #fef3c7; color: #d97706">◎</span>
            {{ translate.no_select_menu_form_msg_5 }}
          </div>
          <div class="help-item">
            <span class="help-icon" style="background-color: #fee2e2; color: #dc2626">✕</span>
            {{ translate.no_select_menu_form_msg_6 }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

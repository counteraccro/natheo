<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Locales, MenuDatas, Translate, Urls, Menu, MenuElement, LoadMenuData } from '@/ts/Menu/type';
import axios from 'axios';
import SkeletonRenderMenu from '@/vue/Components/Skeleton/Menu/MenuRender.vue';
import SkeletonFormMenu from '@/vue/Components/Skeleton/Menu/MenuForm.vue';
import SkeletonArchitectureMenu from '@/vue/Components/Skeleton/Menu/MenuArchitecture.vue';
import MenuTree from '@/vue/Components/Menu/MenuTree.vue';
import Sortable from 'sortablejs';
import Modal from '@/vue/Components/Global/Modal.vue';
import MenuElementForm from '@/vue/Components/Menu/MenuElementForm.vue';
import { emitter } from '@/utils/useEvent';
import Toast from '@/vue/Components/Global/Toast.vue';
import { Toasts } from '@/ts/Toast/type';
import MenuHeader from '@/vue/Components/Menu/MenuType/MenuHeader.vue';
import MenuFooter from '@/vue/Components/Menu/MenuType/MenuFooter.vue';
import MenuLeftRight from '@/vue/Components/Menu/MenuType/MenuLeftRight.vue';

export default defineComponent({
  name: 'Menu',
  components: {
    Toast,
    MenuElementForm,
    Modal,
    MenuTree,
    SkeletonArchitectureMenu,
    SkeletonFormMenu,
    SkeletonRenderMenu,
    MenuHeader,
    MenuFooter,
    MenuLeftRight,
  },
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
      dataMenu: {} as LoadMenuData,
      sortableRoot: null as Sortable | null,
      currentLocale: '',
      listTypeByPosition: {} as Record<string, string>,
      updateNoSave: false,
      idSelected: 0,
      menuElementSelected: null as MenuElement | null,
      nodeToOpen: 0,
      showModalConfirmDelete: false,
      localeValidationState: {} as Record<string, boolean>,
      noSaveElementIds: [] as number[],
      selectComponent: '',
      errors: {
        name: false,
      } as Record<string, boolean>,
      modaleConfirmDelete: {
        title: '',
        body: '',
        params: {
          id: 0,
          isConfirm: false,
        },
      },
      toasts: {
        success: {
          show: false,
          msg: '',
        },
        error: {
          show: false,
          msg: '',
        },
      } as Toasts,
    };
  },
  mounted() {
    this.loadMenu();
    this.currentLocale = this.locales.current;
  },

  computed: {
    /**
     * Retourne les ids des éléments invalides (toutes locales confondues)
     */
    invalidElementIds(): number[] {
      const ids: number[] = [];
      this.collectInvalidIds(this.menu.menuElements ?? [], ids);
      return ids;
    },
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
          if (Object.keys(this.menu).length !== 0) {
            this.normalizeElements(this.menu.menuElements);
            this.dataMenu = response.data.data;
            this.dataMenu.list_target_value = this.menu_datas.list_target_value;
            this.selectListTypeByPosition(this.menu.position);
          }
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
     * Permet de changer de composant
     * @param idPosition
     */
    switchComposant(idPosition: number) {
      switch (idPosition) {
        case 1:
          this.selectComponent = 'MenuHeader';
          break;
        case 2:
        case 4:
          this.selectComponent = 'MenuLeftRight';
          break;
        case 3:
          this.selectComponent = 'MenuFooter';
          break;
        default:
          this.selectComponent = 'MenuHeader';
          break;
      }
    },

    /**
     * Normalise récursivement les children undefined → []
     */
    normalizeElements(elements: MenuElement[]): void {
      elements.forEach((el) => {
        if (el.page === '' || el.page === null) {
          el.page = -10;
        }
        if (!el.children) el.children = [];
        this.normalizeElements(el.children);
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

      this.switchComposant(parseInt(position.toString()));
    },

    /**
     * Chargement du dragandDrop
     */
    loadDraggable(): void {
      this.$nextTick(() => {
        const container = this.$refs.rootListRef as HTMLElement;
        if (!container) return;

        if (this.sortableRoot) {
          this.sortableRoot.destroy();
          this.sortableRoot = null;
        }

        this.sortableRoot = Sortable.create(container, {
          handle: '.drag-handle',
          animation: 200,
          ghostClass: 'opacity-40',
          chosenClass: 'ring-2',
          onEnd: ({ oldIndex, newIndex }) => {
            if (oldIndex === undefined || newIndex === undefined) return;
            if (oldIndex === newIndex) return;

            const items = [...this.menu.menuElements];
            const [moved] = items.splice(oldIndex, 1);
            items.splice(newIndex, 0, moved);

            items.forEach((el, index) => {
              el.rowPosition = index + 1;
            });

            this.menu.menuElements = items;
            this.updateNoSave = true;
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
     * Trouve récursivement un menuElement par son id
     * @param elements
     * @param id
     */
    findElement(elements: MenuElement[], id: number): MenuElement | null {
      for (const el of elements) {
        if (el.id === id) return el;
        if (el.children) {
          const found = this.findElement(el.children, id);
          if (found) return found;
        }
      }
      return null;
    },

    /**
     * Trouve récursivement le tableau children d'un élément par son id
     */
    findChildren(elements: MenuElement[], parentId: number): MenuElement[] | null {
      for (const el of elements) {
        if (el.id === parentId) {
          // Initialise children si absent (éléments venant de l'API)
          if (!el.children) el.children = [];
          return el.children;
        }
        if (el.children) {
          const found = this.findChildren(el.children, parentId);
          if (found) return found;
        }
      }
      return null;
    },

    /**
     * Génère un nouvel élément avec un id temporaire négatif
     * pour le distinguer des éléments existants côté serveur
     */
    newEmptyMenuElement(parentId: number | null = null): MenuElement {
      return {
        id: -Date.now(), // id temporaire négatif
        columnPosition: 1,
        rowPosition: 1,
        linkTarget: '_self',
        disabled: false,
        parent: parentId ?? '',
        page: 0,
        menuElementTranslations: this.locales.locales.map((locale) => ({
          id: 0,
          locale,
          textLink: locale + '-' + -Date.now(),
          externalLink: '',
          link: '',
        })),
        children: [],
      };
    },

    /**
     * Ajout un nouvel menuElement
     * @param parentId
     */
    newMenuElement(parentId: number | null = null): void {
      const newElement = this.newEmptyMenuElement(parentId);

      if (parentId === null) {
        newElement.rowPosition = this.menu.menuElements.length + 1;
        this.menu.menuElements.push(newElement);
      } else {
        const children = this.findChildren(this.menu.menuElements, parentId);
        if (children !== null) {
          newElement.rowPosition = children.length + 1;
          children.push(newElement);
        }
      }

      this.idSelected = newElement.id;
      this.menuElementSelected = newElement;
      this.nodeToOpen = parentId ?? 0;
      this.updateNoSave = true;

      this.$nextTick(() => {
        this.loadDraggable();
      });
    },

    /**
     * Supprime récursivement un élément par son id
     * @param elements
     * @param id
     */
    removeElement(elements: MenuElement[], id: number): boolean {
      const index = elements.findIndex((el) => el.id === id);

      if (index !== -1) {
        elements.splice(index, 1);
        elements.forEach((el, i) => {
          el.rowPosition = i + 1;
        });
        return true;
      }

      // Pas trouvé à ce niveau, on cherche dans les enfants
      for (const el of elements) {
        if (el.children && this.removeElement(el.children, id)) {
          return true;
        }
      }

      return false;
    },

    /**
     * Supprime un menuElement
     * @param id
     * @param isConfirm
     */
    onDelete(id: number, isConfirm: boolean = false): void {
      this.showModalConfirmDelete = false;
      if (!isConfirm) {
        this.modaleConfirmDelete.title = this.translate.menu_element_confirm_delete_title;
        this.modaleConfirmDelete.body =
          this.translate.menu_element_confirm_delete_body +
          '<br />' +
          this.translate.menu_element_confirm_delete_body_2;

        this.modaleConfirmDelete.params.id = id;
        this.modaleConfirmDelete.params.isConfirm = true;

        this.showModalConfirmDelete = true;
        return;
      }

      this.removeElement(this.menu.menuElements, id);
      this.noSaveElementIds = this.noSaveElementIds.filter((el) => el !== id);

      // Si l'élément supprimé était sélectionné, on désélectionne
      if (this.idSelected === id) {
        this.idSelected = 0;
        this.menuElementSelected = null;
      }

      this.updateNoSave = true;
    },

    /**
     * Active ou désactive récursivement un élément et tous ses enfants
     * @param element
     * @param disabled
     */
    toggleVisibilityRecursive(element: MenuElement, disabled: boolean): void {
      element.disabled = disabled;
      if (element.children) {
        element.children.forEach((child) => {
          this.toggleVisibilityRecursive(child, disabled);
        });
      }
    },

    /**
     * Active ou désactive un menuElement et tous ses enfants
     * @param id
     */
    toggleVisibility(id: number): void {
      const element = this.findElement(this.menu.menuElements, id);
      if (element !== null) {
        this.toggleVisibilityRecursive(element, !element.disabled);
        this.updateNoSave = true;
      }
    },

    select(id: number): void {
      const element = this.findElement(this.menu.menuElements, id);
      if (element !== null) {
        this.menuElementSelected = element;
        this.idSelected = element.id;
      }
    },

    /**
     * Sauvegarde un menuElement
     * @param menuElement
     * @param action
     */
    saveMenuElement(menuElement: MenuElement, action: String) {
      const element = this.findElement(this.menu.menuElements, menuElement.id);
      if (element !== null) {
        Object.assign(element, menuElement);

        if (action === 'cancel') {
          this.noSaveElementIds = this.noSaveElementIds.filter((el) => el !== menuElement.id);
        }

        this.updateNoSave = this.noSaveElementIds.length > 0;
        this.menuElementSelected = null;
      }
    },

    /**
     * Parcourt récursivement les éléments et collecte les ids invalides
     */
    collectInvalidIds(elements: MenuElement[], ids: number[]): void {
      elements.forEach((el) => {
        const isInvalid = this.locales.locales.some((locale) => {
          const translation = el.menuElementTranslations.find((t) => t.locale === locale);
          const hasTextLink = !!translation?.textLink?.trim();
          const hasPage = Number(el.page) > 0;
          const hasExternalUrl = translation?.externalLink?.trim() !== '' && translation?.externalLink !== '#';

          return !hasTextLink || (!hasPage && !hasExternalUrl);
        });

        if (isInvalid) ids.push(el.id);

        if (el.children) {
          this.collectInvalidIds(el.children, ids);
        }
      });
    },

    /**
     * Changement d'un menuElement
     * @param id
     */
    onMenuElementChange(id: number): void {
      if (!this.noSaveElementIds.includes(id)) {
        this.noSaveElementIds.push(id);
      }
      this.updateNoSave = true;
    },

    /**
     * Sauvegarde du menu
     */
    saveMenu(): void {
      this.errors.name = !this.menu.name?.trim();
      if (Object.values(this.errors).some(Boolean)) return;

      if (this.invalidElementIds.length > 0) return;
      if (this.menu.name === '') return;

      this.loading = true;
      axios
        .post(this.urls.save_menu, {
          menu: this.menu,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.success.msg = response.data.msg;
            this.toasts.success.show = true;
            // Cas première page, on force la redirection pour passer en mode édition
            if (response.data.redirect === true) {
              window.location.replace(response.data.url);
            }

            this.noSaveElementIds = [];
            this.updateNoSave = false;
          } else {
            this.toasts.error.msg = response.data.msg;
            this.toasts.error.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          emitter.emit('reset-check-confirm');
        });
    },

    /**
     * Ferme la modale de confirmation
     */
    hideModalConfirmDelete() {
      this.showModalConfirmDelete = false;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string) {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <div v-if="loading">
    <skeleton-render-menu />
    <skeleton-form-menu />
    <skeleton-architecture-menu />
  </div>

  <div v-else-if="Object.keys(menu).length === 0">
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
      <!-- Icône -->
      <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
        style="background-color: var(--primary-lighter)"
      >
        <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 7h14M5 12h14M5 17h14" />
        </svg>
      </div>

      <!-- Titre -->
      <p class="text-lg font-bold mb-2" style="color: var(--text-primary)">
        {{ translate.menu_no_exist_title }}
      </p>

      <!-- Description -->
      <p class="text-sm max-w-xs mb-6" style="color: var(--text-secondary)">
        {{ translate.menu_no_exist_text }}
      </p>

      <!-- Boutons -->
      <div class="flex items-center gap-3">
        <a :href="urls.listing" class="btn btn-sm btn-outline-dark flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ translate.btn_back }}
        </a>
        <a :href="urls.new_menu" class="btn btn-sm btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ translate.btn_new }}
        </a>
      </div>
    </div>
  </div>

  <div v-else>
    <div class="card rounded-lg relative overflow-visible mb-5">
      <div class="card-header">
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
              d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"
            />
          </svg>
          {{ translate.title_demo }}
        </div>
      </div>
      <div class="p-4">
        <Component
          v-if="selectComponent"
          :is="selectComponent"
          :menu="menu"
          :type="Number(menu.type)"
          :locale="currentLocale"
          :data="dataMenu"
        />
        <p class="text-xs mt-2 flex items-center gap-1.5" style="color: var(--text-light)">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          {{ translate.title_demo_warning }}
        </p>
      </div>
    </div>

    <!-- Bloc de statut -->
    <div
      v-if="updateNoSave || invalidElementIds.length > 0 || menu.menuElements?.length === 0"
      class="rounded-xl mb-5 px-4 py-3 flex items-center gap-3"
      :style="
        invalidElementIds.length > 0 || menu.menuElements?.length === 0
          ? 'background-color: var(--alert-danger-bg); border: 1px solid var(--alert-danger-border);'
          : 'background-color: var(--alert-warning-bg); border: 1px solid var(--alert-warning-border);'
      "
    >
      <div
        class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
        :style="
          invalidElementIds.length > 0 || menu.menuElements?.length === 0
            ? 'background-color: var(--alert-danger-border);'
            : 'background-color: var(--alert-warning-border);'
        "
      >
        <svg
          v-if="invalidElementIds.length > 0 || menu.menuElements?.length === 0"
          class="w-4 h-4 text-white"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2.5"
            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2.5"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p
          class="text-sm font-semibold"
          :style="
            invalidElementIds.length > 0 || menu.menuElements?.length === 0
              ? 'color: var(--alert-danger-text);'
              : 'color: var(--alert-warning-text);'
          "
        >
          <span v-if="menu.menuElements?.length === 0">
            {{ translate.error_no_menu_element_label }}
          </span>
          <span v-else-if="invalidElementIds.length > 0">
            {{ invalidElementIds.length }} {{ translate.error_info_label }}
          </span>
          <span v-else> {{ translate.no_save_label }} </span>
        </p>
        <p
          class="text-xs mt-0.5"
          :style="
            invalidElementIds.length > 0 || menu.menuElements?.length === 0
              ? 'color: var(--alert-danger-text);'
              : 'color: var(--alert-warning-text);'
          "
        >
          <span v-if="menu.menuElements?.length === 0">
            {{ translate.error_no_menu_element_sub_label }}
          </span>
          <span v-else-if="invalidElementIds.length > 0"> {{ translate.error_info_sub_label }} </span>
          <span v-else> {{ translate.no_save_sub_label }} </span>
        </p>
      </div>
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
          <button
            class="btn btn-sm btn-primary flex items-center gap-2"
            :disabled="
              invalidElementIds.length > 0 || errors.name || menu.menuElements?.length === 0 || menu.name === ''
            "
            @click="saveMenu"
          >
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
                :class="errors.name ? 'is-invalid' : ''"
                id="menu-title"
                v-model="menu.name"
                :placeholder="translate.input_name_placeholder"
                @input="
                  errors.name = !menu.name.trim();
                  errors.name ? '' : (updateNoSave = true);
                "
              />
              <span v-if="errors.name" class="form-text text-error">✗ {{ translate.input_name_error }}</span>
            </div>

            <div>
              <div class="form-switch form-switch-inline">
                <input
                  class="switch-input no-control event-input"
                  type="checkbox"
                  role="switch"
                  id="default_menu"
                  v-model="menu.defaultMenu"
                  @input="updateNoSave = true"
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
                  @input="updateNoSave = true"
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
                @input="updateNoSave = true"
              >
                <option v-for="(position, key) in menu_datas.list_position" :value="key">
                  {{ position }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="menu-title">{{ translate.select_type_label }}</label>
              <select id="menu-type" class="form-input" v-model="menu.type" @input="updateNoSave = true">
                <option v-for="(position, key) in listTypeByPosition" :value="key">{{ position }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-stretch">
      <div class="card rounded-lg overflow-hidden xl:col-span-2 flex flex-col" style="max-height: 680px">
        <div class="px-5 py-4 border-b flex items-center gap-2 shrink-0" style="border-color: var(--border-color)">
          <svg class="w-4 h-4" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
          <span class="text-sm font-semibold" style="color: var(--text-primary)">{{
            translate.title_architecture
          }}</span>
          <div class="ml-auto flex items-center gap-1.5">
            <div
              v-if="invalidElementIds.length > 0"
              class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-colors cursor-pointer bg-(--alert-danger-bg) text-(--alert-danger-text)"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              {{ invalidElementIds.length }}
              {{ translate.error }}
            </div>
          </div>
        </div>

        <div class="p-3 flex-1 overflow-y-auto min-h-0" ref="rootListRef">
          <menu-tree
            v-for="menuElement in menu.menuElements"
            :key="menuElement.id"
            :menu-element="menuElement"
            :translate="translate.menu_tree"
            :locale="currentLocale"
            :id-selected="idSelected"
            :deep="0"
            :force-open="nodeToOpen"
            :invalid-ids="invalidElementIds"
            :no-save-ids="noSaveElementIds"
            @reorder="onReorder"
            @add-child="newMenuElement($event)"
            @delete="onDelete($event)"
            @toggle-visibility="toggleVisibility($event)"
            @select="select($event)"
          />
        </div>
        <div class="p-3">
          <div class="mt-3 shrink-0">
            <button class="btn-add-root" @click="newMenuElement(null)">
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
          <span
            class="text-sm font-semibold"
            style="color: var(--text-primary)"
            v-html="
              menuElementSelected === null
                ? translate.no_select_menu_form
                : translate.no_select_menu_form + ' #' + menuElementSelected.id
            "
          ></span>

          <div v-if="idSelected !== 0" class="ml-auto flex items-center gap-1.5">
            <template v-for="(localeLabel, key) in locales.localesTranslate" :key="key">
              <div
                @click="currentLocale = key"
                :id="localeLabel + '-key'"
                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-colors cursor-pointer"
                :class="[
                  localeValidationState[key]
                    ? 'bg-(--alert-success-bg) text-(--alert-success-text)'
                    : 'bg-(--alert-danger-bg) text-(--alert-danger-text)',
                  currentLocale === key ? 'ring-2 ring-offset-1 ring-(--primary)' : '',
                ]"
              >
                <!-- Valide -->
                <svg
                  v-if="localeValidationState[key]"
                  class="w-3 h-3"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
                <!-- Invalide -->
                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ key.toUpperCase() }}
              </div>
            </template>
          </div>
        </div>

        <div class="edition-empty" v-if="menuElementSelected === null">
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
        <menu-element-form
          v-else
          :translate="translate.menu_form"
          :locale="currentLocale"
          :locales="locales"
          :menu-element="menuElementSelected"
          :menu-data="dataMenu"
          @delete="onDelete($event)"
          @save="saveMenuElement"
          @cancel="saveMenuElement"
          @locale-validation="localeValidationState = $event"
          @on-change="onMenuElementChange($event)"
        />
      </div>
    </div>
  </div>

  <modal
    :id="'confirm-delete-element'"
    :show="showModalConfirmDelete"
    @close-modal="hideModalConfirmDelete"
    :option-show-close-btn="false"
  >
    <template #title> {{ modaleConfirmDelete.title }}</template>
    <template #body>
      <div v-html="modaleConfirmDelete.body"></div>
    </template>
    <template #footer>
      <button
        type="button"
        class="btn btn-primary btn-sm me-2"
        @click="onDelete(modaleConfirmDelete.params.id, modaleConfirmDelete.params.isConfirm)"
      >
        <svg
          class="icon"
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
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>
        {{ translate.menu_element_confirm_delete_btn_ok }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModalConfirmDelete()">
        <svg
          class="icon"
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
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.menu_element_confirm_delete_btn_ko }}
      </button>
    </template>
  </modal>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :type="'success'" :show="toasts.success.show" @close-toast="closeToast('success')">
      <template #body>
        <div v-html="toasts.success.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="toasts.error.show" @close-toast="closeToast('error')">
      <template #body>
        <div v-html="toasts.error.msg"></div>
      </template>
    </toast>
  </div>
</template>

<style scoped></style>

<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Médiathèque
 */

import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonMediatheque from '@/vue/Components/Skeleton/Mediatheque.vue';
import MediasBreadcrumb from '@/vue/Components/Mediatheque/MediasBreadcrumb.vue';
import MediasGrid from '@/vue/Components/Mediatheque/MediasGrid.vue';
import { initFlowbite } from 'flowbite';
import MediaInfo from '@/vue/Components/Mediatheque/MediaInfo.vue';
import { MediaItem, TranslateRecord } from '@/ts/Mediatheque/type';
import MediaEdit from '@/vue/Components/Mediatheque/MediaEdit.vue';
import MediaMove from '@/vue/Components/Mediatheque/MediaMove.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import { Toasts } from '@/ts/Toast/type';
import MediaNew from '@/vue/Components/Mediatheque/MediaNew.vue';
import MediasTrash from '@/vue/Components/Mediatheque/MediasTrash.vue';

export default defineComponent({
  name: 'Mediatheque',
  computed: {},
  components: {
    MediasTrash,
    MediaNew,
    Toast,
    MediaMove,
    MediaEdit,
    MediaInfo,
    MediasGrid,
    MediasBreadcrumb,
    SkeletonMediatheque,
  },
  props: {
    url: String,
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  data() {
    return {
      loading: false,
      folderId: 0,
      filter: 'created_at',
      render: 'grid',
      order: 'asc',
      selectedMedia: {
        type: '',
        id: '',
        title: '',
        description: '',
        name: '',
        currentFolder: '',
        editFolder: '',
      },
      selectedAction: '',
      drawerOpen: false,
      medias: [],
      currentFolder: {
        id: 0,
        root: null,
        size: 0,
      },
      urlActions: {
        nbTrash: '',
        updateTrash: '',
        listTrash: '',
        remove: '',
        saveMediaEdit: '',
        listeMove: '',
        move: '',
        upload: '',
        saveFolder: '',
      },
      canDelete: false,
      nbTrash: 0,
      mediasTrash: [],
      key: 0,
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

  mounted(): any {
    this.loadMedia();
  },

  methods: {
    /** Charge les medias **/
    loadMedia(isOpenDrawer = false): void {
      this.loading = true;
      this.key++;

      if (!isOpenDrawer) {
        this.closeDrawer();
      }
      axios
        .get(this.url + '/' + this.folderId + '/' + this.order + '/' + this.filter)
        .then((response) => {
          this.medias = response.data.medias;
          this.currentFolder = response.data.currentFolder;
          this.urlActions = response.data.url;
          this.canDelete = response.data.canDelete;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.getNbTrash();
          this.loading = false;
          this.$nextTick(() => initFlowbite());
        });
    },

    /**
     * Change le filtre
     * @param filter
     */
    changeFilter(filter: string) {
      this.filter = filter;
      this.loadMedia();
    },

    /**
     * Change l'ordre du tri
     * @param order
     */
    changeOrder(order: string) {
      this.order = order;
      this.loadMedia();
    },

    /**
     * Permet de switcher le mode d'affichage des médias
     * @param render
     */
    switchRender(render: string) {
      if (render === 'trash') {
        this.drawerOpen = false;
      }

      this.render = render;
    },

    /**
     * Retourne le nombre d'éléments dans la corbeille
     */
    getNbTrash(): void {
      axios
        .get(this.urlActions.nbTrash, {})
        .then((response) => {
          this.nbTrash = response.data.nb;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {});
    },

    /**
     * Met à jour un media ou un folder en fonction de la valeur de trash
     */
    updateTrash(trash: boolean, id: string, type: string) {
      axios
        .post(this.urlActions.updateTrash, {
          trash: trash,
          id: id,
          type: type,
        })
        .then(() => {
          this.toasts.success.show = true;
          if (trash) {
            this.toasts.success.msg = this.translate.success_add_trash as string;
          } else {
            this.toasts.success.msg = this.translate.success_remove_trash as string;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          if (trash) {
            this.loadMedia(false);
          } else {
            this.loadInTrash();
          }
        });
    },

    /**
     * Charge les données du dossier en id
     * @param id
     * @param isOpenDrawer
     */
    loadDataInFolder(id: number, isOpenDrawer = false): void {
      this.folderId = id;
      this.loadMedia(isOpenDrawer);
    },

    /**
     * Nouveau dossier
     */
    newFolder(): void {
      let tmp = {
        name: '',
        type: 'folder',
        thumbnail: '/assets/natheo/mediatheque/folder.svg',
        id: 0,
        parent: this.currentFolder.id,
      };
      this.openBlockDrawer(tmp, 'new-folder');
    },

    /**
     * Nouveau média
     */
    newMedia(): void {
      this.openBlockDrawer({}, 'new-media');
    },

    /**
     * OUvre le panneau de droite
     * @param media
     * @param action
     */
    openBlockDrawer(media: any, action: string): void {
      document.querySelectorAll('[data-dropdown-toggle]').forEach((btn) => {
        const id = btn.getAttribute('data-dropdown-toggle');
        // @ts-expect-error
        const dropdown = FlowbiteInstances.getInstance('Dropdown', id);
        if (dropdown) dropdown.hide();
      });
      this.selectedMedia = media;
      this.selectedAction = action;
      this.drawerOpen = true;
      this.key++;
    },

    /**
     * Ferme me panneau de droite
     */
    closeDrawer(): void {
      this.drawerOpen = false;
    },

    /**
     * Rechargement de la médiatheque
     * @param id
     * @param isOpenDrawer
     * @param data
     */
    reload(id: number, isOpenDrawer: boolean, data: any) {
      if (this.selectedMedia.type === 'media') {
        this.selectedMedia.id = data.id;
        this.selectedMedia.title = data.name;
        this.selectedMedia.description = data.description;
      } else {
        this.selectedMedia.name = data.name;
        this.selectedMedia.currentFolder = data.currentFolder;
        this.selectedMedia.editFolder = data.editFolder;
      }

      this.loadDataInFolder(id, isOpenDrawer);
    },

    /**
     * Charge le contenu de la corbeille
     */
    loadInTrash() {
      this.loading = true;
      axios
        .get(this.urlActions.listTrash)
        .then((response) => {
          this.mediasTrash = response.data.mediasTrash;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
          this.$nextTick(() => initFlowbite());
        });
    },

    /**
     * Supprime un média ou un folder de façon définitive
     */
    remove(id: number, type: string) {
      this.loading = true;
      axios
        .post(this.urlActions.remove, {
          id: id,
          type: type,
        })
        .then(() => {
          this.toasts.success.show = true;
          // @ts-expect-error
          this.toasts.success.msg = this.translate.remove.success as string;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loadInTrash();
          this.getNbTrash();
        });
    },

    /**
     * Event de la sourie
     * @param event
     * @param color
     */
    onMenuHover(event: MouseEvent, color: string) {
      const link = (event.target as HTMLElement)?.closest('a') as HTMLElement | null;
      if (link) {
        link.style.backgroundColor = color;
      }
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
  <skeleton-mediatheque v-if="loading" />

  <div v-else id="block-mediatheque" class="card rounded-xl overflow-hidden">
    <div
      class="flex items-center justify-between px-4 py-3 border-b"
      style="border-color: var(--border-color); background-color: var(--bg-main)"
    >
      <MediasBreadcrumb v-if="render !== 'trash'" :paths="currentFolder.root" @load-folder="loadDataInFolder" />
      <div v-else class="flex items-center gap-0 text-sm">
        <button
          class="flex items-center gap-1.5 px-2 py-1 rounded-md font-medium transition text-[var(--primary)] bg-[var(--primary-lighter)] cursor-pointer"
        >
          <svg
            class="w-4 h-4"
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
              d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"
            />
          </svg>

          {{ translate.header_trash }}
        </button>
      </div>

      <!-- Storage info -->
      <div class="flex items-center gap-2">
        <div
          class="hidden sm:flex items-center gap-2 text-xs px-3 py-1.5 rounded-full"
          style="background-color: var(--bg-hover); color: var(--text-secondary)"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"
            ></path>
          </svg>
          <span class="font-semibold" v-html="render === 'trash' ? nbTrash : currentFolder.size"> </span>
          <span
            style="color: var(--text-light)"
            v-html="render === 'trash' ? translate.nb_file_trash : translate.disque_size"
          ></span>
        </div>
      </div>
    </div>

    <div
      v-if="render !== 'trash'"
      class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-[var(--border-color)]"
    >
      <div class="flex items-center gap-2">
        <button class="btn btn-xs btn-primary inline-flex items-center gap-2 px-3 py-2" @click="newFolder">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
            ></path>
          </svg>
          {{ translate.btn_new_folder }}
        </button>
        <button class="btn btn-xs btn-outline-dark inline-flex items-center gap-2 px-3 py-2" @click="newMedia">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
            ></path>
          </svg>
          {{ translate.btn_new_media }}
        </button>
      </div>

      <div class="flex items-center gap-2">
        <!-- Search -->
        <!--<div class="relative md:hidden">
          <input
            type="search"
            class="search-input pl-8 pr-3 py-2 rounded-lg border text-sm w-40"
            style="border-color: var(--border-color); background-color: var(--bg-card); color: var(--text-primary)"
            placeholder="Rechercher…"
          />
          <svg
            class="w-4 h-4 absolute left-2 top-2.5 pointer-events-none"
            style="color: var(--text-light)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            ></path>
          </svg>
        </div> -->

        <div class="relative">
          <button
            id="sortBtn"
            data-dropdown-toggle="sortDropdown"
            class="btn btn-xs btn-outline-dark inline-flex items-center gap-2 px-3 py-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
            <span class="hidden sm:inline">Trier par</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div
            id="sortDropdown"
            class="z-50 dropdown-menu rounded-lg shadow-lg w-48 hidden"
            data-popper-placement="bottom"
            style="
              background-color: var(--bg-card);
              border: 1px solid var(--border-color);
              box-shadow: var(--shadow-md);
              position: absolute;
              inset: 0px auto auto 0px;
              margin: 0px;
              transform: translate3d(687.273px, 497.273px, 0px);
            "
          >
            <a
              href="#"
              @click="changeFilter('created_at')"
              class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm"
              @mouseover="onMenuHover($event, 'var(--bg-hover)')"
              @mouseleave="onMenuHover($event, '')"
            >
              <svg
                v-if="filter === 'created_at'"
                class="w-4 h-4"
                style="color: var(--primary)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              {{ translate.filtre_date }}
            </a>
            <a
              href="#"
              @click="changeFilter('name')"
              class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm"
              @mouseover="onMenuHover($event, 'var(--bg-hover)')"
              @mouseleave="onMenuHover($event, '')"
            >
              <svg
                v-if="filter === 'name'"
                class="w-4 h-4"
                style="color: var(--primary)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              {{ translate.filtre_nom }}
            </a>
            <a
              @click="changeFilter('type')"
              href="#"
              class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm"
              @mouseover="onMenuHover($event, 'var(--bg-hover)')"
              @mouseleave="onMenuHover($event, '')"
            >
              <svg
                v-if="filter === 'type'"
                class="w-4 h-4"
                style="color: var(--primary)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              {{ translate.filtre_type }}</a
            >
          </div>
        </div>

        <!-- Sort direction -->
        <button
          class="btn btn-xs btn-outline-dark p-2"
          title="Inverser l'ordre"
          @click="changeOrder(order === 'asc' ? 'desc' : 'asc')"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="
                order === 'desc'
                  ? 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12'
                  : 'M3 4h13M3 8h9m-9 4h6m8-4v12m0 0l-4-4m4 4l4-4'
              "
            ></path>
          </svg>
        </button>

        <!-- Delete selected -->
        <button
          class="btn btn-xs btn-outline-danger p-2 relative"
          title="Supprimer la sélection"
          @click="
            switchRender('trash');
            loadInTrash();
          "
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
          <span
            v-if="nbTrash > 0"
            class="absolute -top-1.5 -right-1.5 min-w-4.5 h-4.5 flex items-center justify-center rounded-full text-white font-bold px-1"
            style="font-size: 0.6rem; background-color: var(--btn-danger)"
          >
            {{ nbTrash > 99 ? '99+' : nbTrash }}
          </span>
        </button>

        <div class="btn-group">
          <button
            class="btn btn-outline-dark btn-xs"
            @click="switchRender('grid')"
            :style="
              render === 'grid'
                ? 'background-color: var(--btn-dark); border-color: var(--btn-dark); color: #ffffff'
                : ''
            "
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
              ></path>
            </svg>
          </button>
          <button
            class="btn btn-outline-dark btn-xs"
            @click="switchRender('list')"
            :style="
              render === 'list'
                ? 'background-color: var(--btn-dark); border-color: var(--btn-dark); color: #ffffff'
                : ''
            "
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 10h16M4 14h16M4 18h16"
              ></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
    <div
      v-else
      class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-[var(--border-color)]"
    >
      <div class="flex items-center gap-2"></div>
      <div class="flex items-center gap-2"></div>
      <div class="relative">
        <button
          class="btn btn-xs btn-outline-dark p-2"
          title="Inverser l'ordre"
          @click="
            switchRender('grid');
            loadMedia();
          "
        >
          <svg
            class="w-4 h-4"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              fill-rule="evenodd"
              d="M4 4a2 2 0 0 0-2 2v12a2 2 0 0 0 .087.586l2.977-7.937A1 1 0 0 1 6 10h12V9a2 2 0 0 0-2-2h-4.532l-1.9-2.28A2 2 0 0 0 8.032 4H4Zm2.693 8H6.5l-3 8H18l3-8H6.693Z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>
    </div>

    <div class="browser-inner">
      <div class="browser-content">
        <medias-grid
          v-if="render !== 'trash'"
          :render="render"
          :medias="medias"
          :translate="translate.media as TranslateRecord"
          @load-data-folder="loadDataInFolder"
          @show-info="openBlockDrawer"
          @edit="openBlockDrawer"
          @move="openBlockDrawer"
          @trash="updateTrash"
        >
        </medias-grid>

        <MediasTrash
          v-else
          :translate="translate.trash as TranslateRecord"
          :medias="mediasTrash"
          @revert-trash="updateTrash"
          @delete="remove"
        >
        </MediasTrash>
      </div>
      <div class="info-drawer" id="infoDrawer" :class="drawerOpen ? 'open' : ''">
        <media-info
          :key="'MI-' + key"
          v-if="selectedAction === 'show'"
          :translate="translate.info as TranslateRecord"
          :data="selectedMedia as unknown as MediaItem"
          @close="closeDrawer"
        />

        <media-edit
          :key="'ME-' + key"
          v-if="selectedAction === 'edit' || selectedAction === 'new-folder'"
          :translate="translate.edit as TranslateRecord"
          :data="selectedMedia as unknown as MediaItem"
          :url="selectedMedia.type === 'media' ? urlActions.saveMediaEdit : urlActions.saveFolder"
          @close="closeDrawer"
          @reload="reload"
        />

        <media-move
          :key="'ME-' + key"
          v-if="selectedAction === 'move'"
          :translate="translate.move as TranslateRecord"
          :data="selectedMedia as unknown as MediaItem"
          :urls="{
            move: urlActions.move,
            listeMove: urlActions.listeMove,
          }"
          @close="closeDrawer"
          @reload="reload"
        />

        <media-new
          :key="'ME-' + key"
          v-if="selectedAction === 'new-media'"
          :translate="translate.upload as TranslateRecord"
          :url="urlActions.upload"
          :current-folder="currentFolder.id"
          @reload="reload"
          @close="closeDrawer"
        />
      </div>
    </div>
    <div
      class="px-4 py-3 border-t flex items-center justify-between text-xs border-[var(--border-color)] text-[var(--text-secondary)] bg-[var(--bg-main)]"
    >
      <div>
        <span v-html="render === 'trash' ? translate.header_trash : ''"></span>
      </div>
      <span
        v-html="
          render === 'trash'
            ? nbTrash + ' ' + translate.nb_file_trash
            : currentFolder.size + ' ' + translate.disque_size
        "
      ></span>
    </div>
  </div>

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

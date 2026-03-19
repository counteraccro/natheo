<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonMediatheque from '@/vue/Components/Skeleton/Mediatheque.vue';
import MediasBreadcrumb from '@/vue/Components/Mediatheque/MediasBreadcrumb.vue';
import MediasGrid from '@/vue/Components/Mediatheque/MediasGrid.vue';
import { initFlowbite } from 'flowbite';
import MediaInfo from '@/vue/Components/Mediatheque/MediaInfo.vue';
import { MediaItem, TranslateRecord } from '@/ts/Mediatheque/type';
import MediaEdit from '@/vue/Components/Mediatheque/MediaEdit.vue';

export default defineComponent({
  name: 'Mediatheque',
  components: { MediaEdit, MediaInfo, MediasGrid, MediasBreadcrumb, SkeletonMediatheque },
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
      selectedMedia: {},
      selectedAction: '',
      drawerOpen: false,
      medias: [],
      currentFolder: [],
      urlActions: Object as PropType<Record<string, string>>,
      canDelete: false,
      nbTrash: 0,
      key: 0,
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
     * Charge les données du dossier en id
     * @param id
     * @param isOpenDrawer
     */
    loadDataInFolder(id: number, isOpenDrawer = false): void {
      this.folderId = id;
      this.loadMedia(isOpenDrawer);
    },

    openBlockDrawer(media: any, action: string): void {
      document.querySelectorAll('[data-dropdown-toggle]').forEach((btn) => {
        const id = btn.getAttribute('data-dropdown-toggle');
        const dropdown = FlowbiteInstances.getInstance('Dropdown', id);
        if (dropdown) dropdown.hide();
      });
      this.selectedMedia = media;
      this.selectedAction = action;
      this.drawerOpen = true;
      this.key++;
    },

    closeDrawer(): void {
      this.drawerOpen = false;
    },

    reload(id: number, isOpenDrawer: boolean, data: any) {
      if (this.selectedMedia.type === 'media') {
        this.selectedMedia.title = data.name;
        this.selectedMedia.description = data.description;
      } else {
        this.selectedMedia.name = data.name;
      }

      this.loadDataInFolder(id, isOpenDrawer);
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
      <MediasBreadcrumb :paths="currentFolder.root" @load-folder="loadDataInFolder" />

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
          <span class="font-semibold"> {{ currentFolder.size }}</span>
          <span style="color: var(--text-light)">{{ translate.disque_size }}</span>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-[var(--border-color)]">
      toolbar
    </div>

    <div class="browser-inner">
      <div class="browser-content">
        <div class="p-4">
          <medias-grid
            :render="render"
            :medias="medias"
            :translate="translate.media as TranslateRecord"
            @load-data-folder="loadDataInFolder"
            @show-info="openBlockDrawer"
            @edit="openBlockDrawer"
            @move="loadListFolderMove"
            @trash="confirmTrash"
          >
          </medias-grid>
        </div>
      </div>
      <div class="info-drawer" id="infoDrawer" :class="drawerOpen ? 'open' : ''">
        <media-info
          :key="'MI-' + key"
          v-if="selectedAction === 'show'"
          :translate="translate.info as TranslateRecord"
          :data="selectedMedia as MediaItem"
          @close="closeDrawer"
        />

        <media-edit
          :key="'ME-' + key"
          v-if="selectedAction === 'edit'"
          :translate="translate.edit as TranslateRecord"
          :data="selectedMedia as MediaItem"
          :url="selectedMedia.type === 'media' ? urlActions.saveMediaEdit : urlActions.saveFolder"
          @close="closeDrawer"
          @reload="reload"
        />
      </div>
    </div>
  </div>
</template>

<style scoped></style>

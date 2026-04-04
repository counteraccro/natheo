<script lang="ts">
/**
 * MediaPickerModal.vue
 *
 * Modale de sélection d'un média depuis la médiathèque Nathéo.
 * S'ouvre via le CustomEvent `natheo:open-media` dispatché par MediaModule.
 *
 * Intégration dans le composant parent (ex. EditPage.vue) :
 *   <MediaPickerModal :url-media="urls.media" />
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

import axios from 'axios';
import Modal from '@/vue/Components/Global/Modal.vue';
import type { MediaFile, NatheoMediaEvent } from '@/ts/MarkdownEditor/modules/Mediatheque';

interface MediaItem {
  id: number;
  name: string;
  url: string; // webPath — inséré dans le markdown
  thumb: string; // thumbnail — affiché dans la modale
  isImage: boolean;
}

interface FolderItem {
  id: number;
  name: string;
}

const IMAGE_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.avif'];

function checkIsImage(filename: string): boolean {
  const ext = filename.match(/\.[a-z0-9]+$/i)?.[0]?.toLowerCase() ?? '';
  return IMAGE_EXTENSIONS.includes(ext);
}

export default {
  name: 'MediaPickerModal',

  components: {
    Modal,
  },

  props: {
    /** URL Symfony pour charger les médias : /admin/fr/media/ajax/{folderId}/{order}/{filter} */
    urlMedia: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      /** Modale ouverte ou non */
      isOpen: false,

      /** Callback fourni par le module via CustomEvent */
      onSelectCallback: null as ((media: MediaFile) => void) | null,

      /** Médias chargés depuis Symfony */
      medias: [] as MediaItem[],

      /** Dossiers chargés */
      folders: [] as FolderItem[],

      /** Dossier courant (0 = racine) */
      currentFolderId: 0,

      /** Chemin de navigation (breadcrumb) */
      breadcrumb: [] as FolderItem[],

      /** Chargement en cours */
      loading: false,

      /** Filtre texte local */
      search: '',

      /** Ordre de tri */
      order: 'asc' as 'asc' | 'desc',

      /** Champ de tri */
      filter: 'created_at',
    };
  },

  computed: {
    filteredMedias(): MediaItem[] {
      if (!this.search.trim()) return this.medias;
      const q = this.search.toLowerCase();
      return this.medias.filter((m) => m.name.toLowerCase().includes(q));
    },

    imageMedias(): MediaItem[] {
      return this.filteredMedias.filter((m) => m.isImage);
    },

    fileMedias(): MediaItem[] {
      return this.filteredMedias.filter((m) => !m.isImage);
    },
  },

  mounted() {
    window.addEventListener('natheo:open-media', this.handleOpenMedia as EventListener);
  },

  beforeUnmount() {
    window.removeEventListener('natheo:open-media', this.handleOpenMedia as EventListener);
  },

  methods: {
    // ─── Gestion de l'event ────────────────────────────────────────────────

    handleOpenMedia(event: NatheoMediaEvent): void {
      this.onSelectCallback = event.detail.onSelect;
      this.open();
    },

    // ─── Ouverture / fermeture ─────────────────────────────────────────────

    open(): void {
      this.search = '';
      this.currentFolderId = 0;
      this.breadcrumb = [];
      this.isOpen = true;
      this.loadMedias(0);
    },

    close(): void {
      this.isOpen = false;
      this.onSelectCallback = null;
      this.medias = [];
      this.folders = [];
    },

    // ─── Chargement depuis Symfony ─────────────────────────────────────────

    loadMedias(folderId: number): void {
      this.loading = true;
      this.currentFolderId = folderId;

      axios
        .get(`${this.urlMedia}/${folderId}/${this.order}/${this.filter}`)
        .then(({ data }) => {
          // L'API retourne un tableau plat, chaque item a un champ `type`
          // `type: "folder"` → dossier (navigation uniquement)
          // `type: "media"`  → fichier sélectionnable (insertion dans l'éditeur)
          const items: any[] = Array.isArray(data) ? data : Object.values(data.medias);

          this.folders = items
            .filter((item: any) => item.type === 'folder')
            .map((f: any) => ({
              id: f.id,
              name: f.name,
            }));

          this.medias = items
            .filter((item: any) => item.type === 'media')
            .map((m: any) => ({
              id: m.id,
              name: m.title ?? m.name,
              url: m.webPath,
              thumb: m.thumbnail,
              isImage: checkIsImage(m.name),
            }));
        })
        .catch(console.error)
        .finally(() => {
          this.loading = false;
        });
    },

    // ─── Navigation dossiers ──────────────────────────────────────────────

    openFolder(folder: FolderItem): void {
      this.breadcrumb.push(folder);
      this.loadMedias(folder.id);
    },

    goToBreadcrumb(index: number): void {
      this.breadcrumb = this.breadcrumb.slice(0, index + 1);
      const folder = this.breadcrumb[index];
      this.loadMedias(folder.id);
    },

    goToRoot(): void {
      this.breadcrumb = [];
      this.loadMedias(0);
    },

    // ─── Sélection d'un média ─────────────────────────────────────────────

    selectMedia(media: MediaItem): void {
      if (!this.onSelectCallback) return;

      const file: MediaFile = {
        name: media.name,
        url: media.url,
        type: media.isImage ? 'image' : 'file',
        alt: media.name,
      };

      this.onSelectCallback(file);
      this.close();
    },
  },
};
</script>

<template>
  <modal :id="'modal-media-picker'" :show="isOpen" @close-modal="close" :option-show-close-btn="true">
    <!-- Icône -->
    <template #icon>
      <svg class="h-6 w-6 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2" />
        <circle cx="8.5" cy="8.5" r="1.5" stroke-width="2" />
        <polyline points="21 15 16 10 5 21" stroke-width="2" />
      </svg>
    </template>

    <!-- Titre -->
    <template #title>Médiathèque</template>

    <!-- Corps -->
    <template #body>
      <!-- Breadcrumb + Recherche -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm flex-1 min-w-0 flex-wrap">
          <button
            class="flex items-center gap-1.5 px-3 py-1 rounded-lg font-medium transition-opacity hover:opacity-75"
            style="background-color: var(--primary-lighter); color: var(--primary)"
            @click="goToRoot"
          >
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
            </svg>
            <span>Root</span>
          </button>

          <template v-for="(crumb, index) in breadcrumb" :key="crumb.id">
            <svg
              class="w-4 h-4 flex-shrink-0"
              style="color: var(--text-light)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <button
              class="flex items-center gap-1.5 px-3 py-1 rounded-lg font-medium transition-opacity hover:opacity-75"
              style="background-color: var(--primary-lighter); color: var(--primary)"
              @click="goToBreadcrumb(index)"
            >
              <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
              </svg>
              <span class="truncate max-w-[120px]">{{ crumb.name }}</span>
            </button>
          </template>
        </div>

        <!-- Recherche -->
        <div class="relative flex-shrink-0 w-full sm:w-56">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4" style="color: var(--text-light)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </div>
          <input
            v-model="search"
            type="search"
            placeholder="Rechercher..."
            class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border focus:outline-none focus:ring-2 transition-all"
            style="background-color: var(--bg-card); color: var(--text-primary); border-color: var(--border-color)"
          />
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
          <div
            v-for="i in 12"
            :key="i"
            class="h-20 rounded-lg animate-pulse"
            style="background-color: var(--bg-hover)"
          />
        </div>
      </template>

      <template v-else>
        <!-- Dossiers -->
        <div v-if="folders.length > 0" class="mb-6">
          <h4 class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-light)">Dossiers</h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <button
              v-for="folder in folders"
              :key="folder.id"
              class="flex items-center gap-3 px-4 py-3 rounded-lg border-2 border-dashed transition-all text-left"
              style="border-color: var(--border-dark); background-color: var(--bg-main); color: var(--text-primary)"
              @mouseenter="($event.currentTarget as HTMLElement).style.borderColor = 'var(--primary)'"
              @mouseleave="($event.currentTarget as HTMLElement).style.borderColor = 'var(--border-dark)'"
              @click="openFolder(folder)"
            >
              <svg class="w-5 h-5 flex-shrink-0" style="color: var(--primary)" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
              </svg>
              <span class="text-sm font-medium truncate flex-1">{{ folder.name }}</span>
              <svg
                class="w-4 h-4 flex-shrink-0"
                style="color: var(--text-light)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Images -->
        <div v-if="imageMedias.length > 0" class="mb-6">
          <h4 class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-light)">Images</h4>
          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
            <button
              v-for="media in imageMedias"
              :key="media.id"
              class="group relative h-20 rounded-lg overflow-hidden border transition-all hover:opacity-90 focus:outline-none focus:ring-2"
              style="border-color: var(--border-color); --tw-ring-color: var(--primary)"
              :title="media.name"
              @click="selectMedia(media)"
            >
              <img :src="media.thumb" :alt="media.name" class="w-full h-full object-cover" />
              <div
                class="absolute inset-0 flex flex-col items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                style="background-color: rgba(0, 0, 0, 0.55)"
              >
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-white text-xs font-semibold px-2 text-center leading-tight line-clamp-2">
                  {{ media.name }}
                </span>
              </div>
            </button>
          </div>
        </div>

        <!-- Fichiers (non-images) -->
        <div v-if="fileMedias.length > 0">
          <h4 class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-light)">Fichiers</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button
              v-for="media in fileMedias"
              :key="media.id"
              class="flex items-center gap-3 px-4 py-3 rounded-lg border transition-all text-left hover:opacity-80 focus:outline-none focus:ring-2"
              style="
                border-color: var(--border-color);
                background-color: var(--bg-main);
                color: var(--text-primary);
                --tw-ring-color: var(--primary);
              "
              :title="media.name"
              @click="selectMedia(media)"
            >
              <svg
                class="w-5 h-5 flex-shrink-0"
                style="color: var(--text-secondary)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              <span class="text-sm truncate">{{ media.name }}</span>
              <svg
                class="w-4 h-4 flex-shrink-0 ml-auto"
                style="color: var(--primary)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>
        </div>

        <!-- État vide -->
        <div
          v-if="folders.length === 0 && filteredMedias.length === 0"
          class="flex flex-col items-center justify-center py-16 gap-4"
        >
          <svg class="w-14 h-14" style="color: var(--text-light)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="1.5" />
            <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5" />
            <polyline points="21 15 16 10 5 21" stroke-width="1.5" />
          </svg>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">
            {{ search ? 'Aucun résultat pour « ' + search + ' »' : 'Ce dossier est vide' }}
          </p>
        </div>
      </template>
    </template>

    <!-- Footer -->
    <template #footer>
      <span class="text-sm" style="color: var(--text-secondary)">
        {{ filteredMedias.length }} fichier{{ filteredMedias.length > 1 ? 's' : '' }}
      </span>
    </template>
  </modal>
</template>

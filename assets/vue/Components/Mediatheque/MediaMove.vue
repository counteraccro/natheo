<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet de déplacer un média ou une image
 */
import { defineComponent, type PropType } from 'vue';
import { MediaItem, TranslateRecord } from '@/ts/Mediatheque/type';
import SkeletonMediathequeMove from '@/vue/Components/Skeleton/MediathequeMove.vue';
import axios from 'axios';
import { Toasts } from '@/ts/Toast/type';
import Toast from '@/vue/Components/Global/Toast.vue';

export default defineComponent({
  name: 'MediaMove',
  components: { Toast, SkeletonMediathequeMove },
  props: {
    data: { type: Object as PropType<MediaItem>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    urls: { type: Object, required: true },
  },
  emits: ['close', 'reload'],
  data() {
    return {
      move: false,
      isError: false,
      loading: true,
      dataMove: {
        listeFolder: [] as { id: number; name: string }[],
      },
      selectedId: -10,
      search: '',
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
    this.loadListFolderMove(this.data.type, this.data.id);
  },
  computed: {
    /**
     * Construction de l'arborescence des dossiers
     */
    parsedFolders() {
      return this.dataMove.listeFolder.map((folder: { name: string }) => {
        const match = folder.name.match(/^(\|[-]*)(.+)$/);
        if (!match) {
          return { ...folder, depth: 0, label: folder.name };
        }
        const depth = match[1].replace('|', '').length + 1;
        const label = match[2].trim();
        return { ...folder, depth, label };
      });
    },

    /**
     * Filtre les dossiers selon la recherche
     */
    filteredFolders(): any {
      if (!this.search.trim()) return this.parsedFolders;
      const q = this.search.toLowerCase();
      return this.parsedFolders.filter((f: { label: string }) => f.label.toLowerCase().includes(q));
    },
  },
  methods: {
    /**
     * Récupération de la liste des dossiers
     * @param type
     * @param id
     */
    loadListFolderMove(type: string, id: number): void {
      this.loading = true;
      axios
        .get(this.urls.listeMove + '/' + id + '/' + type)
        .then((response) => {
          this.dataMove = response.data.dataMove;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Déplace un média
     */
    moveMedia() {
      this.loading = true;
      axios
        .post(this.urls.move, {
          idToMove: this.selectedId,
          id: this.data.id,
          type: this.data.type,
        })
        .then(() => {
          this.loading = false;
          this.toasts.success.show = true;
          this.toasts.success.msg = this.translate.move_success as string;
          this.move = true;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          setTimeout(() => {
            this.$emit('reload', this.selectedId, false, this.data);
          }, 2000);
        });
    },

    /**
     * Dossier sélectionné
     * @param folder
     */
    selectFolder(folder: any) {
      this.selectedId = folder.id;
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
  <div class="info-drawer-inner">
    <skeleton-mediatheque-move v-if="loading" />

    <div v-else>
      <!-- Header du panneau -->
      <div class="flex items-center justify-between mb-4">
        <h3 class="flex gap-1 items-center font-semibold text-sm" style="color: var(--text-primary)">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
            ></path>
          </svg>
          {{ translate.title }}
        </h3>
        <button
          @click="$emit('close')"
          class="p-1.5 rounded-lg transition"
          style="color: var(--text-secondary)"
          onmouseover="this.style.backgroundColor = 'var(--bg-hover)'"
          onmouseout="this.style.backgroundColor = ''"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <p class="text-xs mb-3" style="color: var(--text-secondary)">
        <span v-if="data.type === 'media'">
          {{ translate.sub_title_media }}
        </span>
        <span v-else>
          {{ translate.sub_title_folder }}
        </span>
        <b> {{ data.name }} </b>
        {{ translate.sub_title_end }}
      </p>

      <div class="input-group mb-5">
        <svg class="icon icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          ></path>
        </svg>
        <input
          type="search"
          class="form-input input-icon-left"
          :placeholder="translate.search_placeholder as string"
          v-model="search"
        />
      </div>

      <template v-if="parsedFolders.length > 0">
        <div class="folder-tree mb-3 max-h-48 overflow-y-auto">
          <div
            v-if="filteredFolders.length > 0"
            v-for="folder in filteredFolders"
            :key="folder.id"
            class="folder-tree-item"
            :class="{ selected: folder.id === selectedId }"
            :style="{ paddingLeft: folder.depth * 16 + 8 + 'px' }"
            @click="selectFolder(folder)"
          >
            <span
              v-if="folder.depth > 0"
              class="tree-line"
              :style="{ left: (folder.depth - 1) * 16 + 12 + 'px' }"
            ></span>

            <svg
              class="w-4 h-4 flex-shrink-0"
              fill="currentColor"
              viewBox="0 0 24 24"
              :style="{ color: folder.id === selectedId ? 'var(--primary)' : 'var(--text-light)' }"
            >
              <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.11-.9-2-2-2h-8l-2-2z" />
            </svg>

            <span class="flex-1 truncate">{{ folder.label }}</span>

            <svg
              v-if="folder.id === selectedId"
              class="w-3.5 h-3.5 flex-shrink-0"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-6 gap-2">
            <svg class="w-8 h-8" style="color: var(--text-light)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <p class="text-xs font-medium" style="color: var(--text-secondary)">{{ translate.msg_no_search }}</p>
            <p class="text-xs" style="color: var(--text-light)">{{ translate.msg_no_search_sub }}</p>
          </div>
        </div>
      </template>

      <!-- Actions -->
      <div class="flex flex-col gap-2">
        <button
          @click="moveMedia"
          class="btn btn-sm"
          :disabled="move || selectedId === -10"
          :class="isError ? 'btn-danger' : !move ? 'btn-primary' : 'btn-success'"
          v-html="isError ? '✘ ' + translate.btn_error : !move ? translate.btn_move : '✓ ' + translate.move_ok"
        ></button>
      </div>
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

<style scoped>
/* ── Conteneur ── */
.folder-tree {
  padding: 0.25rem;
  border-radius: 0.5rem;
  background-color: var(--bg-main);
  border: 1px solid var(--border-color);
}

/* ── Item ── */
.folder-tree-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.3rem 0.6rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.8rem;
  color: var(--text-primary);
  transition: background-color 0.15s;
  user-select: none;
}

.folder-tree-item:hover {
  background-color: var(--bg-hover);
}

.folder-tree-item.selected {
  background-color: var(--primary-lighter);
  color: var(--primary);
  font-weight: 600;
}

/* ── Lignes de connexion ── */
.tree-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 1px;
  background-color: var(--border-color);
  pointer-events: none;
}
</style>

<script lang="ts">
import { initFlowbite } from 'flowbite';
import type { PropType } from 'vue';
import { MediaItem, TranslateRecord } from '@/ts/Mediatheque/type';

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID des médias dans la corbeille
 */
export default {
  name: 'MediaTrash',
  props: {
    medias: { type: Array as PropType<MediaItem[]>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  emits: ['revert-trash', 'delete'],
  data() {
    return {
      confirmDeleteId: null as number | null,
    };
  },
  computed: {},
  methods: {
    /**
     * Permet d'ouvrir un média
     * @param path
     */
    openMedia(path: string) {
      window.open(path, '_blank');
    },

    /**
     * Demande de confirmation
     * @param id
     */
    askDelete(id: number): void {
      this.confirmDeleteId = id;
    },

    /**
     * Annulation
     */
    cancelDelete(): void {
      this.confirmDeleteId = null;
      this.$nextTick(() => initFlowbite());
    },

    /**
     * Suppression
     * @param id
     * @param type
     */
    confirmDelete(id: number, type: string): void {
      this.$emit('delete', id, type);
      this.confirmDeleteId = null;
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
  },
};
</script>

<template>
  <div
    id="block-media-grid"
    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 p-4"
  >
    <div
      v-if="medias.length > 0"
      class="media-card"
      v-for="media in medias"
      @click="media.type === 'media' ? openMedia(media.webPath) : ''"
    >
      <template v-if="confirmDeleteId === media.id">
        <div class="media-thumb">
          <div
            class="absolute inset-0 flex flex-col items-center justify-center gap-3 p-4 text-center"
            style="background-color: var(--alert-danger-bg)"
          >
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
              style="background-color: var(--alert-danger-bg); border: 1px solid var(--alert-danger-border)"
            >
              <svg
                class="w-5 h-5"
                style="color: var(--btn-danger)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
            </div>
            <p class="text-xs font-semibold" style="color: var(--alert-danger-text)">
              {{ translate.confirm_trash_title }}
            </p>
          </div>
        </div>

        <div
          class="media-meta gap-2"
          style="background-color: var(--alert-danger-bg); border-top: 1px solid var(--alert-danger-border)"
        >
          <button
            @click.stop="confirmDelete(media.id, media.type)"
            class="flex-1 flex items-center justify-center gap-1 btn btn-xs btn-danger"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            {{ translate.confirm_trash_yes }}
          </button>
          <button
            @click.stop="cancelDelete()"
            class="flex-1 flex items-center justify-center gap-1 btn btn-xs btn-outline-dark"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ translate.confirm_trash_no }}
          </button>
        </div>
      </template>

      <template v-else>
        <div class="media-thumb">
          <img class="absolute inset-0 w-full h-full object-cover" :src="media.thumbnail" />
          <div v-if="media.type === 'folder'" class="folder-pattern">
            <div
              class="w-full h-full flex flex-col"
              :class="media.nb_elements > 0 ? 'p-3 justify-end gap-1.5' : 'items-center justify-center gap-2'"
            >
              <span
                v-if="media.nb_elements === 0"
                class="text-sm font-semibold mt-5"
                style="color: var(--primary); opacity: 0.9"
                >{{ translate.folder_empty }}</span
              >
            </div>
          </div>
          <span
            class="type-badge"
            :class="media.type === 'folder' ? 'folder' : ''"
            v-html="media.type === 'media' ? media.extension : translate.folder_tag"
          ></span>
        </div>
        <div class="media-meta">
          <div class="media-meta-info">
            <div
              class="media-name"
              v-html="media.type === 'media' ? media.name + '.' + media.extension : media.name"
            ></div>
          </div>
          <button
            :id="`dropdownBtnTrash-${media.id}`"
            :data-dropdown-toggle="`dropdownTrash-${media.id}`"
            @click="$event.stopPropagation()"
            class="ctx-footer-btn"
            type="button"
          >
            ...
          </button>

          <Teleport to="body">
            <div
              :id="`dropdownTrash-${media.id}`"
              class="z-50 hidden rounded-xl shadow-xl w-44 py-0 text-sm overflow-hidden"
              style="
                background-color: var(--bg-card);
                border: 1px solid var(--border-color);
                box-shadow: var(--shadow-md);
              "
            >
              <ul :aria-labelledby="`dropdownBtnTrash-${media.id}`">
                <li>
                  <a
                    href="#"
                    @click="$emit('revert-trash', false, media.id, media.type)"
                    class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150"
                    style="color: var(--text-primary)"
                    @mouseover="onMenuHover($event, 'var(--bg-hover)')"
                    @mouseleave="onMenuHover($event, '')"
                  >
                    <svg
                      class="w-4 h-4 flex-shrink-0"
                      style="color: var(--text-secondary)"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"
                      />
                    </svg>
                    {{ translate.link_revert }}
                  </a>
                </li>
                <li style="border-top: 1px solid var(--border-color)">
                  <a
                    href="#"
                    @click="askDelete(media.id)"
                    class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150 text-red-500"
                    @mouseover="onMenuHover($event, '#fef2f2')"
                    @mouseleave="onMenuHover($event, '')"
                  >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                    {{ translate.link_delete }}
                  </a>
                </li>
              </ul>
            </div>
          </Teleport>
        </div>
      </template>
    </div>
  </div>

  <div v-if="medias.length === 0" class="flex flex-col items-center justify-center gap-3 py-16 text-center">
    <div
      class="w-14 h-14 rounded-2xl flex items-center justify-center"
      style="background-color: var(--bg-hover); border: 1px solid var(--border-color)"
    >
      <svg class="w-6 h-6" style="color: var(--text-light)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="1.5"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
        />
      </svg>
    </div>
    <p class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.empty_trash_title }}</p>
    <p class="text-xs" style="color: var(--text-secondary)">{{ translate.empty_trash_sub }}</p>
  </div>

  <!--<div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
      <img
        v-if="media.type === 'media'"
        height="200"
        width="200"
        class="rounded-3"
        :src="media.thumbnail"
        style="cursor: pointer"
        :alt="media.name"
        @click="this.openMedia(media.webPath)"
      />
      <div v-else class="folder" alt="media.name"></div>
      <div class="info-media rounded-bottom-3">
        <div class="btn-group">
          <button
            type="button"
            class="btn btn-link btn-sm dropdown-toggle pt-2"
            style="color: #ffffff; margin-top: -5px"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="bi bi-justify"></i>
          </button>
          <ul class="dropdown-menu">
            <li>

              <a class="dropdown-item" @click="$emit('revert-trash', media.type, media.id)">
                <i class="bi bi-arrow-counterclockwise"></i> {{ this.translate.link_revert }}
              </a>
            </li>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <li>
              <a class="dropdown-item text-danger" @click="$emit('delete', media.type, media.id, media.name, false)">
                <i class="bi bi-trash-fill"></i> {{ this.translate.link_delete }}
              </a>
            </li>
          </ul>
        </div>
        <span class="d-inline-block text-truncate" style="max-width: 140px; vertical-align: middle">
          {{ media.name }}
        </span>
      </div>
    </div>
    <div v-else class="text-center mt-3">
      <i class="bi bi-folder-x"></i>
      <i>
        {{ this.translate.no_media }}
      </i>
    </div>
  </div>-->
</template>

<style scoped></style>

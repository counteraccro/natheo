<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Affichage au format GRID et listing des médias
 */

import { defineComponent, type PropType } from 'vue';
import * as events from 'node:events';
import { MediaItem, TranslateRecord } from '@/ts/Mediatheque/type';
import { initFlowbite } from 'flowbite';

export default defineComponent({
  name: 'MediasGrid',
  props: {
    medias: { type: Array as PropType<MediaItem[]>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    render: String,
  },
  emits: ['load-data-folder', 'edit', 'show-info', 'move', 'trash'],
  data() {
    return {
      confirmTrashId: null as number | null,
    };
  },
  computed: {
    events() {
      return events;
    },
  },
  methods: {
    /**
     * Permet d'ouvrir un média
     * @param path
     */
    openMedia(path: string): void {
      window.open(path, '_blank');
    },

    selectItem(el: HTMLElement) {
      el.classList.toggle('selected');
      const icon = el.querySelector('.check-icon');
      if (icon) icon.classList.toggle('hidden', !el.classList.contains('selected'));
      const card = el.closest('.media-card');
      card?.classList.toggle('selected');
    },

    /**
     * Demande de confirmation
     * @param id
     */
    askTrash(id: number): void {
      this.confirmTrashId = id;
    },

    /**
     * Annulation
     */
    cancelTrash(): void {
      this.confirmTrashId = null;
      this.$nextTick(() => initFlowbite());
    },

    /**
     * Suppression
     * @param id
     * @param type
     */
    confirmTrash(id: number, type: string): void {
      this.$emit('trash', true, id, type);
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
});
</script>

<template>
  <div
    id="block-media-grid"
    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 p-4"
    v-if="render === 'grid'"
  >
    <div
      v-if="medias.length > 0"
      class="media-card"
      :style="confirmTrashId === media.id ? 'background-color: var(--alert-danger-bg)' : ''"
      v-for="media in medias"
      @click="media.type === 'media' ? openMedia(media.webPath) : $emit('load-data-folder', media.id)"
    >
      <template v-if="confirmTrashId === media.id">
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
            @click.stop="confirmTrash(media.id, media.type)"
            class="flex-1 flex items-center justify-center gap-1 btn btn-xs btn-danger"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            {{ translate.confirm_trash_yes }}
          </button>
          <button
            @click.stop="cancelTrash()"
            class="flex-1 flex items-center justify-center gap-1 btn btn-xs btn-outline-dark"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ translate.confirm_trash_no }}
          </button>
        </div>
      </template>
      <div v-else>
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
              <div
                v-if="media.nb_elements > 0"
                class="flex justify-end gap-1 rounded-md overflow-hidden flex-shrink-0"
                style="height: 28px"
              >
                <span v-for="subMedia in media.children" class="block w-full h-full overflow-hidden">
                  <img
                    v-if="subMedia.type === 'media'"
                    :src="subMedia.thumbnail"
                    class="w-full h-full object-cover"
                    style="width: 100%"
                    alt="aa"
                  />
                </span>
                <div
                  class="flex items-center justify-center text-xs font-bold"
                  style="background-color: var(--primary); color: white; width: 100%"
                  v-html="
                    media.nb_elements > 2
                      ? '+' + (media.nb_elements - media.children.filter((child) => child.type === 'media').length)
                      : media.nb_elements
                  "
                ></div>
              </div>
            </div>
          </div>
          <div
            class="media-check"
            @click="
              $event.stopPropagation();
              selectItem($event.currentTarget as HTMLElement);
            "
          >
            <svg class="w-3 h-3 text-white hidden check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
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
              v-html="media.type === 'media' ? media.title + '.' + media.extension : media.name"
            ></div>
            <div class="media-size">
              <span v-if="media.type === 'media'"
                >{{ media.size }} · {{ media.img_size === '--' ? translate.media_other_img : media.img_size }}</span
              >
              <span v-if="media.type === 'folder'"
                >{{ media.size }} · {{ media.nb_elements }}
                {{ media.nb_elements === 0 ? translate.folder_nb_element : translate.folder_nb_elements }}</span
              >
            </div>
          </div>
          <button
            :id="`dropdownBtn-${media.id}`"
            :data-dropdown-toggle="`dropdown-${media.id}`"
            @click="$event.stopPropagation()"
            class="ctx-footer-btn"
            type="button"
          >
            ...
          </button>

          <Teleport to="body">
            <div
              :id="`dropdown-${media.id}`"
              class="z-50 hidden rounded-xl shadow-xl w-44 py-0 text-sm overflow-hidden"
              style="
                background-color: var(--bg-card);
                border: 1px solid var(--border-color);
                box-shadow: var(--shadow-md);
              "
            >
              <ul :aria-labelledby="`dropdownBtn-${media.id}`">
                <li>
                  <a
                    href="#"
                    class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150"
                    style="color: var(--text-primary)"
                    @click="$emit('show-info', media, 'show')"
                    @mouseover="onMenuHover($event, 'var(--bg-hover)')"
                    @mouseleave="onMenuHover($event, '')"
                  >
                    <svg
                      class="w-4 h-4 flex-shrink-0"
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
                    {{ translate.link_info }}
                  </a>
                </li>
                <li>
                  <a
                    href="#"
                    class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150"
                    style="color: var(--text-primary)"
                    @click="$emit('edit', media, 'edit')"
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
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                      />
                    </svg>
                    {{ translate.link_edit }}
                  </a>
                </li>
                <li>
                  <a
                    href="#"
                    class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150"
                    style="color: var(--text-primary)"
                    @click="$emit('move', media, 'move')"
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
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                      />
                    </svg>
                    {{ translate.link_move }}
                  </a>
                </li>
                <li style="border-top: 1px solid var(--border-color)">
                  <a
                    href="#"
                    @click="askTrash(media.id)"
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
                    {{ translate.link_remove }}
                  </a>
                </li>
              </ul>
            </div>
          </Teleport>
        </div>
      </div>
    </div>
  </div>

  <div v-if="render === 'list'">
    <table class="w-full text-sm">
      <thead style="background-color: var(--bg-main)">
        <tr>
          <th
            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider w-8"
            style="color: var(--text-secondary)"
          ></th>
          <th
            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
            style="color: var(--text-secondary)"
          >
            {{ translate.table_name }}
          </th>
          <th
            class="hidden sm:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
            style="color: var(--text-secondary)"
          >
            {{ translate.table_type }}
          </th>
          <th
            class="hidden md:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
            style="color: var(--text-secondary)"
          >
            {{ translate.table_size }}
          </th>
          <th
            class="hidden lg:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
            style="color: var(--text-secondary)"
          >
            {{ translate.table_date }}
          </th>
          <th
            class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider"
            style="color: var(--text-secondary)"
          >
            {{ translate.table_action }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="list-row" v-if="medias.length > 0" v-for="media in medias">
          <td class="pl-4 py-3">
            <div v-if="media.type === 'folder'" class="w-8 h-8 rounded-lg folder-bg flex items-center justify-center">
              <svg class="folder-icon w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.097.903 2 2 2h16c1.097 0 2-.903 2-2V8c0-1.11-.9-2-2-2h-8l-2-2z"
                ></path>
              </svg>
            </div>
            <div v-else class="w-8 h-8 rounded-lg overflow-hidden">
              <img :src="media.thumbnail" class="w-full h-full object-cover" alt="" />
            </div>
          </td>
          <td class="px-4 py-3 font-medium text-[var(--text-primary)]">
            <a
              class="link text-sm"
              style="cursor: pointer"
              @click.stop="media.type === 'media' ? openMedia(media.webPath) : $emit('load-data-folder', media.id)"
              >{{ media.name }}</a
            >
          </td>
          <td class="hidden sm:table-cell px-4 py-3 text-[var(--text-secondary)]">{{ media.type }}</td>
          <td class="hidden sm:table-cell px-4 py-3 text-[var(--text-secondary)]">{{ media.size }}</td>
          <td class="hidden sm:table-cell px-4 py-3 text-[var(--text-secondary)]">{{ media.date }}</td>
          <td class="px-4 py-3 text-right">
            <button
              class="no-control no-control btn btn-ghost-primary btn-icon"
              style="color: var(--text-primary)"
              @click="$emit('show-info', media, 'show')"
              @mouseover="onMenuHover($event, 'var(--bg-hover)')"
              @mouseleave="onMenuHover($event, '')"
            >
              <svg
                class="w-4 h-4 flex-shrink-0"
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
            </button>

            <button
              class="no-control no-control btn btn-ghost-primary btn-icon"
              style="color: var(--text-primary)"
              @click="$emit('edit', media, 'edit')"
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
                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                />
              </svg>
            </button>

            <button
              class="no-control no-control btn btn-ghost-primary btn-icon"
              style="color: var(--text-primary)"
              @click="$emit('move', media, 'move')"
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
                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                />
              </svg>
            </button>

            <button
              @click="$emit('trash', true, media.id, media.type)"
              class="no-control btn btn-ghost-danger btn-icon"
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
            </button>
          </td>
        </tr>
      </tbody>
    </table>
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
          d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
        />
      </svg>
    </div>
    <p class="text-sm font-semibold" style="color: var(--text-primary)">{{ translate.empty_folder_title }}</p>
    <p class="text-xs" style="color: var(--text-secondary)">{{ translate.empty_folder_sub }}</p>
  </div>
</template>

<style scoped>
.list-row {
  background-color: var(--bg-card);
  border-bottom: 1px solid var(--border-color);
  transition: background-color 0.15s ease;
}
.list-row:hover {
  background-color: var(--bg-hover);
}
.list-row:last-child {
  border-bottom: none;
}
</style>

<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Affichage au format GRID et listing des médias
 */

import { defineComponent, type PropType } from 'vue';
import * as events from 'node:events';
import { MediaItem, TranslateRecord } from '@/ts/Mediatheque/type';

export default defineComponent({
  name: 'MediasGrid',
  props: {
    medias: Array as PropType<MediaItem[]>,
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    render: String,
  },
  emits: ['load-data-folder', 'edit', 'show-info', 'move', 'trash'],
  data() {
    return {};
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
  },
});
</script>

<template>
  <div
    id="block-media-grid"
    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3"
    v-if="render === 'grid'"
  >
    <div
      v-if="medias.length > 0"
      class="media-card"
      v-for="media in medias"
      @click="media.type === 'media' ? openMedia(media.webPath) : $emit('load-data-folder', media.id)"
    >
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
                  @mouseover="$event.target.closest('a').style.backgroundColor = 'var(--bg-hover)'"
                  @mouseleave="$event.target.closest('a').style.backgroundColor = ''"
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
                  @mouseover="$event.target.closest('a').style.backgroundColor = 'var(--bg-hover)'"
                  @mouseleave="$event.target.closest('a').style.backgroundColor = ''"
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
                  @mouseover="$event.target.closest('a').style.backgroundColor = 'var(--bg-hover)'"
                  @mouseleave="$event.target.closest('a').style.backgroundColor = ''"
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
                  @click="$emit('trash', true, media.id, media.type)"
                  class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150 text-red-500"
                  @mouseover="$event.target.closest('a').style.backgroundColor = '#fef2f2'"
                  @mouseleave="$event.target.closest('a').style.backgroundColor = ''"
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

  <!-- <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
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
      <div v-else class="folder" alt="media.name" @click="$emit('load-data-folder', media.id)"></div>
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
              <!-- lien information --
              <a class="dropdown-item" @click="$emit('show-info', media.type, media.id)">
                <i class="bi bi-info-circle-fill"></i> {{ this.translate.link_info }}
              </a>
            </li>
            <li>
              <!-- lien éditer --
              <a v-if="media.type === 'media'" class="dropdown-item" @click="$emit('edit-media', media.id)">
                <i class="bi bi-pencil-square"></i> {{ this.translate.link_edit_media }}
              </a>
              <a v-else class="dropdown-item" @click="$emit('edit-folder', media.id)">
                <i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}
              </a>
            </li>
            <li>
              <a class="dropdown-item" @click="$emit('move', media.type, media.id)">
                <i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.link_move }}
              </a>
            </li>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <li>
              <a class="dropdown-item" @click="$emit('trash', media.type, media.id, media.name, false)">
                <i class="bi bi-trash-fill"></i> {{ this.translate.link_remove }}
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
  </div>
  <div v-else>
    <div class="table-responsive mt-5">
      <table class="table table-striped table-hover align-middle">
        <caption>
          <div class="btn btn-secondary btn-sm float-end">{{ this.translate.table_caption }}</div>
        </caption>
        <thead>
          <tr>
            <th style="width: 3%">#</th>
            <th>{{ this.translate.table_name }}</th>
            <th style="width: 15%">{{ this.translate.table_date }}</th>
            <th style="width: 10%">{{ this.translate.table_size }}</th>
            <th style="width: 20%">{{ this.translate.table_action }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="this.medias.length > 0" v-for="media in this.medias">
            <td>
              <span v-if="media.type === 'media'"><i class="bi bi-file-fill"></i> </span>
              <span v-else><i class="bi bi-folder-fill"></i> </span>
            </td>
            <td>
              <a
                class="link-info"
                style="cursor: pointer"
                @click.stop="
                  media.type === 'media' ? this.openMedia(media.webPath) : $emit('load-data-folder', media.id)
                "
                >{{ media.name }}</a
              >
            </td>
            <td>{{ media.date }}</td>
            <td>
              <span v-if="media.type === 'media'">{{ media.size }}</span>
            </td>
            <td>
              <a
                class="btn btn-sm btn-secondary me-1 mt-1"
                @click="media.type === 'media' ? this.openMedia(media.webPath) : $emit('load-data-folder', media.id)"
              >
                <i class="bi" :class="media.type === 'media' ? 'bi-eye' : 'bi-folder2-open'"></i>
              </a>

              <!-- lien information --
              <a class="btn btn-sm btn-secondary me-1 mt-1" @click="$emit('show-info', media.type, media.id)">
                <i class="bi bi-info-circle"></i>
              </a>

              <!-- lien éditer --
              <a
                class="btn btn-sm btn-secondary me-1 mt-1"
                @click="media.type === 'media' ? $emit('edit-media', media.id) : $emit('edit-folder', media.id)"
              >
                <i class="bi bi-pencil-square"></i>
              </a>

              <!-- lien move --
              <a class="btn btn-sm btn-secondary me-1 mt-1" @click="$emit('move', media.type, media.id)">
                <i class="bi bi-arrow-right-circle"></i>
              </a>

              <!-- lien corbeille --
              <a class="btn btn-sm btn-secondary mt-1" @click="$emit('trash', media.type, media.id, media.name, false)">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
          <tr v-else>
            <td colspan="4" class="text-center">
              {{ this.translate.no_media }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div> -->
</template>

<style scoped></style>

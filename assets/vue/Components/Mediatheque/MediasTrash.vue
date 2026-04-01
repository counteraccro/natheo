<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID des médias dans la corbeille
 */
export default {
  name: 'MediaTrash',
  props: {
    medias: Object,
    translate: Object,
  },
  emits: ['revert-trash', 'delete'],
  data() {
    return {};
  },
  computed: {},
  methods: {
    /**
     * Permet d'ouvrir un média
     * @param path
     */
    openMedia(path) {
      window.open(path, '_blank');
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
                  class="no-control flex items-center gap-2.5 px-3 py-2.5 transition-colors duration-150"
                  style="color: var(--text-primary)"
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
                      d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"
                    />
                  </svg>
                  {{ translate.link_revert }}
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
                  {{ translate.link_delete }}
                </a>
              </li>
            </ul>
          </div>
        </Teleport>
      </div>
    </div>
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

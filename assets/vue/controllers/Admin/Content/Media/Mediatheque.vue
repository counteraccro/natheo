<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonMediatheque from '@/vue/Components/Skeleton/Mediatheque.vue';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'Mediatheque',
  components: { SkeletonMediatheque },
  props: {
    url: String,
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  data() {
    return {
      loading: false,
      folderId: 0,
      filter: 'created_at',
      order: 'asc',
      medias: [],
      currentFolder: [],
      urlActions: Object as PropType<Record<string, string>>,
      canDelete: false,
      nbTrash: 0,
    };
  },

  mounted(): any {
    this.loadMedia();
  },

  methods: {
    /** Charge les medias **/
    loadMedia(): void {
      this.loading = true;
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
  },
});
</script>

<template>
  <skeleton-mediatheque v-if="loading" />

  <div v-else class="card rounded-xl overflow-hidden">
    <div
      class="flex items-center justify-between px-4 py-3 border-b"
      style="border-color: var(--border-color); background-color: var(--bg-main)"
    >
      <!-- Breadcrumb path -->
      <div class="flex items-center gap-1 text-sm">
        <button
          class="flex items-center gap-1.5 px-2 py-1 rounded-md font-medium transition"
          style="color: var(--primary); background-color: var(--primary-lighter)"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
            ></path>
          </svg>
          Accueil
        </button>
        <svg class="w-3.5 h-3.5" style="color: var(--text-light)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <button
          class="px-2 py-1 rounded-md font-medium hover:underline transition"
          style="color: var(--text-secondary)"
        >
          médias
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
          <span class="font-semibold"> {{ currentFolder.size }}</span>
          <span style="color: var(--text-light)">utilisés</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>

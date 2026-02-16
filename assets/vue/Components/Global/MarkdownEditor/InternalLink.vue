<script lang="ts">
import { defineComponent, ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import type { InternalPage, NatheoInternalLinkEvent } from '@/ts/MarkdownEditor/internalLinkModule';
import Modal from '@/vue/Components/Global/Modal.vue'; // adapte le chemin

export default defineComponent({
  name: 'InternalLink',

  components: { Modal },

  props: {
    url: {
      type: String,
      default: '',
    },
  },

  setup(props) {
    const isOpen = ref<boolean>(false);
    const query = ref<string>('');
    const pages = ref<InternalPage[]>([]);
    const searchRef = ref<HTMLInputElement | null>(null);

    let onSelectCallback: ((page: InternalPage) => void) | null = null;

    // ── Chargement des pages ──────────────────────────────────────────────────

    async function loadPages(): Promise<void> {
      if (!props.url || pages.value.length > 0) return;
      const { data } = await axios.get<InternalPage[]>(props.url);
      pages.value = data;
    }

    // ── Écoute l'événement du module ──────────────────────────────────────────

    function handleOpen(e: NatheoInternalLinkEvent): void {
      onSelectCallback = e.detail.onSelect;
      query.value = '';
      isOpen.value = true;
      loadPages();
    }

    onMounted(() => {
      window.addEventListener('natheo:open-internal-link', handleOpen);
    });

    onUnmounted(() => {
      window.removeEventListener('natheo:open-internal-link', handleOpen);
    });

    // ── Focus auto à l'ouverture ──────────────────────────────────────────────

    watch(isOpen, async (open) => {
      if (open) {
        await nextTick();
        searchRef.value?.focus();
      }
    });

    // ── Filtrage ──────────────────────────────────────────────────────────────

    const filteredPages = computed<InternalPage[]>(() => {
      const q = query.value.trim().toLowerCase();
      if (!q) return pages.value;
      return pages.value.filter((p) => p.title.toLowerCase().includes(q) || p.url.toLowerCase().includes(q));
    });

    // ── Actions ───────────────────────────────────────────────────────────────

    function selectPage(page: InternalPage): void {
      onSelectCallback?.(page);
      close();
    }

    function close(): void {
      isOpen.value = false;
      onSelectCallback = null;
    }

    return {
      isOpen,
      query,
      searchRef,
      filteredPages,
      selectPage,
      close,
    };
  },
});
</script>

<template>
  <modal :id="'modal-internal-link'" :show="isOpen" @close-modal="close" :option-show-close-btn="true">
    <template #icon>
      <svg class="h-6 w-6 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"
        />
        <polyline points="13 2 13 9 20 9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
      </svg>
    </template>

    <template #title>Insérer un lien interne</template>

    <template #body>
      <!-- Recherche -->
      <input
        ref="searchRef"
        v-model="query"
        type="search"
        class="form-control mb-3"
        placeholder="Rechercher une page…"
      />

      <!-- Liste des pages -->
      <div v-if="filteredPages.length === 0" class="text-muted fst-italic text-center py-3">
        {{ query ? 'Aucune page trouvée' : 'Chargement…' }}
      </div>

      <div class="list-group">
        <button
          v-for="page in filteredPages"
          :key="page.id"
          type="button"
          class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
          @click="selectPage(page)"
        >
          <div>
            <div class="fw-semibold">{{ page.title }}</div>
            <small class="text-muted font-monospace">{{ page.url }}</small>
          </div>
          <span v-if="page.locale" class="badge" style="background-color: var(--primary)">
            {{ page.locale }}
          </span>
        </button>
      </div>
    </template>

    <template #footer>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="close">Annuler</button>
    </template>
  </modal>
</template>

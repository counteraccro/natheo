<script lang="ts">
import { defineComponent, ref, computed, watch, nextTick, onMounted, onUnmounted, type PropType } from 'vue';
import axios from 'axios';
import type { InternalPage, NatheoInternalLinkEvent } from '@/ts/MarkdownEditor/internalLinkModule';
import Modal from '@/vue/Components/Global/Modal.vue';
import { props } from '@vue/language-core/lib/codegen/names'; // adapte le chemin

export default defineComponent({
  name: 'InternalLink',
  methods: {
    props() {
      return props;
    },
  },

  components: { Modal },

  props: {
    url: {
      type: String,
      default: '',
    },
    translate: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
  },

  data() {
    return {};
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
      const { data } = await axios.get(props.url);
      console.log('internalLinks response:', data);
      console.log('type:', typeof data, Array.isArray(data));
      pages.value = Object.values(data.pages ?? {}) as InternalPage[];
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
      if (!q || q === '') return pages.value;
      return pages.value.filter((p) => p.title.toLowerCase().includes(q));
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

    <template #title>{{ translate.title }}</template>

    <template #body>
      <input
        ref="searchRef"
        v-model="query"
        type="search"
        class="form-input mb-3"
        :placeholder="translate.labelSearch"
      />

      <div class="ilp-list">
        <div v-if="filteredPages.length === 0" class="ilp-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
          {{ query ? translate.noResult : 'Chargement…' }}
        </div>

        <ul v-else>
          <li v-for="page in filteredPages" :key="page.id" class="ilp-item" @click="selectPage(page)">
            <span class="ilp-item-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z" />
                <polyline points="13 2 13 9 20 9" />
                <path d="M9 14h6M9 17h3" />
              </svg>
            </span>
            <span class="ilp-item-title">{{ page.title }}</span>
            <span class="ilp-item-arrow">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
            </span>
          </li>
        </ul>
      </div>
    </template>

    <template #footer>
      <span class="text-sm text-[var(--text-secondary)]">
        {{ filteredPages.length }} {{ translate.statistique }}
      </span></template
    >
  </modal>
</template>

<style>
.ilp-list {
  max-height: 18rem;
  overflow-y: auto;
  margin: 0 -1rem; /* déborde légèrement du padding modal */
  scrollbar-width: thin;
  scrollbar-color: var(--border-dark) transparent;
}

ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.ilp-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition:
    background-color 0.15s ease,
    border-color 0.15s ease;
  position: relative;
}

.ilp-item:hover {
  background-color: var(--bg-hover);
  border-left-color: var(--primary);
}

.ilp-item:hover .ilp-item-icon {
  color: var(--primary);
}

.ilp-item:hover .ilp-item-arrow {
  opacity: 1;
  transform: translateX(0);
}

.ilp-item-icon {
  flex-shrink: 0;
  width: 1.125rem;
  height: 1.125rem;
  color: var(--text-light);
  transition: color 0.15s ease;
}

.ilp-item-icon svg {
  width: 100%;
  height: 100%;
}

.ilp-item-title {
  flex: 1;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ilp-item-arrow {
  flex-shrink: 0;
  width: 1rem;
  height: 1rem;
  color: var(--primary);
  opacity: 0;
  transform: translateX(-6px);
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}

.ilp-item-arrow svg {
  width: 100%;
  height: 100%;
}

.ilp-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 2rem 1rem;
  color: var(--text-light);
  font-size: 0.875rem;
  font-style: italic;
}

.ilp-empty svg {
  width: 2rem;
  height: 2rem;
  opacity: 0.4;
}
</style>

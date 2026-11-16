/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pages publiées d'une catégorie, des plus récemment modifiées aux plus anciennes, chargées page après page
 */
import { computed, ref, shallowRef, watch, type ComputedRef, type Ref, type ShallowRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { PageCategory, PageSummary } from '@/ts/Front/Api/Page.type';

export interface CategoryPages {
  /** Pages déjà chargées, dans l'ordre de l'API */
  items: ShallowRef<PageSummary[]>;
  /** Nombre total de pages de la catégorie */
  total: Ref<number>;
  hasMore: ComputedRef<boolean>;
  loading: Ref<boolean>;
  error: ShallowRef<unknown>;
  loadMore(): Promise<void>;
  pageUrl(summary: PageSummary): string;
}

/**
 * @param category catégorie lue, null pour aucune liste
 * @param itemsPerPage pages chargées à chaque appel
 */
export function useCategoryPages(category: Ref<PageCategory | null>, itemsPerPage = 20): CategoryPages {
  const context = useFrontContext();
  const items = shallowRef<PageSummary[]>([]);
  const total = ref(0);
  const lastPage = ref(0);
  const loaded = ref(0);
  const loading = ref(false);
  const error = shallowRef<unknown>(null);
  // Écarte la réponse d'une catégorie quittée entre-temps
  let generation = 0;

  async function load(page: number): Promise<void> {
    const current = category.value;
    if (current === null) {
      return;
    }
    const request = generation;
    loading.value = true;
    error.value = null;
    try {
      const result = await context.api.getCollection<PageSummary>('/pages', { category: current, page, itemsPerPage });
      if (request !== generation) {
        return;
      }
      items.value = page === 1 ? result.members : [...items.value, ...result.members];
      total.value = result.total;
      lastPage.value = result.lastPage;
      loaded.value = page;
    } catch (e) {
      if (request === generation) {
        error.value = e;
      }
    } finally {
      if (request === generation) {
        loading.value = false;
      }
    }
  }

  watch(
    category,
    () => {
      generation++;
      items.value = [];
      total.value = 0;
      lastPage.value = 0;
      loaded.value = 0;
      loading.value = false;
      load(1);
    },
    { immediate: true }
  );

  return {
    items,
    total,
    hasMore: computed(() => loaded.value < lastPage.value),
    loading,
    error,
    loadMore: () => (loading.value ? Promise.resolve() : load(loaded.value + 1)),
    pageUrl: (summary: PageSummary) => context.pageUrl(summary.category, summary.slug),
  };
}

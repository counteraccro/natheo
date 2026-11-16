/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pages d'un bloc listing, paginées
 */
import { ref, type Ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useAsync, type AsyncState } from '@/vue/Composables/Front/useAsync';
import type { Paginated } from '@/ts/Front/Api/Hydra.type';
import type { PageSummary } from '@/ts/Front/Api/Page.type';
import type { ListingContent } from '@/ts/Front/Api/PageContent.type';

export interface Listing extends AsyncState<Paginated<PageSummary>> {
  page: Ref<number>;
  goTo(page: number): Promise<void>;
  pageUrl(summary: PageSummary): string;
}

export function useListing(content: ListingContent, itemsPerPage = 20): Listing {
  const context = useFrontContext();
  const state = useAsync<Paginated<PageSummary>>();
  const page = ref(1);

  async function goTo(target: number): Promise<void> {
    page.value = target;
    await state.run(() => context.api.getCollection<PageSummary>(content.pages, { page: target, itemsPerPage }));
  }

  goTo(1);
  return {
    ...state,
    page,
    goTo,
    pageUrl: (summary: PageSummary) => context.pageUrl(summary.category, summary.slug),
  };
}

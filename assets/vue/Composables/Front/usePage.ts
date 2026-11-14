/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Page affichée : par son slug, page d'accueil sans slug, ou brouillon prévisualisé
 */
import { useFrontContext, type FrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useAsync, type AsyncState } from '@/vue/Composables/Front/useAsync';
import { ApiError } from '@/vue/Composables/Front/Api/ApiError';
import type { Page, PageSummary } from '@/ts/Front/Api/Page.type';

export function usePage(): AsyncState<Page> {
  const context = useFrontContext();
  const state = useAsync<Page>();

  state
    .run(() => context.once('page', () => loadPage(context)))
    .then((page) => {
      if (page !== null) {
        document.title = page.title;
        context.page.value = page;
      }
    });
  return state;
}

async function loadPage(context: FrontContext): Promise<Page> {
  if (context.datas.preview !== null) {
    return context.datas.preview.page;
  }

  const query = context.datas.slug ? { slug: context.datas.slug } : { landing: true };
  const found = await context.api.getCollection<PageSummary>('/pages', query);
  const summary = found.members[0];
  if (summary === undefined) {
    throw new ApiError(404, 'Not Found');
  }

  const page = await context.api.get<Page>(summary['@id']);
  // Le compteur de lectures ne doit pas bloquer l'affichage
  context.api.post('/page_reads', { page: page['@id'] }).catch(() => undefined);
  return page;
}

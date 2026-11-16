/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Page affichée : celle du slug du contexte (accueil sans slug), ou brouillon prévisualisé ;
 * suit les changements de slug de la navigation sans rechargement
 */
import { ref, shallowRef, watch, type Ref, type ShallowRef } from 'vue';
import { useFrontContext, type FrontContext } from '@/vue/Composables/Front/useFrontContext';
import { ApiError } from '@/vue/Composables/Front/Api/ApiError';
import type { Meta, Page, PageSummary } from '@/ts/Front/Api/Page.type';

export interface PageState {
  /** Dernière page chargée, gardée pendant le chargement de la suivante */
  data: ShallowRef<Page | null>;
  loading: Ref<boolean>;
  error: ShallowRef<unknown>;
}

export function usePage(): PageState {
  const context = useFrontContext();
  const data = shallowRef<Page | null>(null);
  const loading = ref(false);
  const error = shallowRef<unknown>(null);
  // Seule la dernière page demandée est affichée
  let request = 0;

  async function show(slug: string | null): Promise<void> {
    const current = ++request;
    loading.value = true;
    error.value = null;
    try {
      const page = await context.once('page:' + (slug ?? ''), () => loadPage(context, slug));
      if (current !== request) {
        return;
      }
      const previous = context.page.value;
      if (previous !== null && previous.id !== page.id) {
        replaceMetas(previous.seo, page.seo);
      }
      document.title = page.title;
      data.value = page;
      context.page.value = page;
    } catch (e) {
      if (current === request) {
        error.value = e;
      }
    } finally {
      if (current === request) {
        loading.value = false;
      }
    }
  }

  watch(context.slug, show, { immediate: true });
  return { data, loading, error };
}

async function loadPage(context: FrontContext, slug: string | null): Promise<Page> {
  if (context.datas.preview !== null) {
    return context.datas.preview.page;
  }

  const query = slug ? { slug } : { landing: true };
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

/**
 * Métas SEO de la page quittée remplacées par celles de la page ouverte (les métas robots du site restent)
 */
function replaceMetas(previous: Meta[], next: Meta[]): void {
  const pageMetas = (metas: Meta[]) => metas.filter((meta) => meta.name !== 'robots');
  for (const meta of pageMetas(previous)) {
    document.head.querySelector(`meta[name="${CSS.escape(meta.name)}"]`)?.remove();
  }
  for (const meta of pageMetas(next)) {
    const element = document.createElement('meta');
    element.name = meta.name;
    element.content = meta.value;
    document.head.append(element);
  }
}

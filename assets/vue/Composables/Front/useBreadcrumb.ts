/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fil d'Ariane de la page affichée
 */
import { computed, type ComputedRef, type Ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Page } from '@/ts/Front/Api/Page.type';

export interface BreadcrumbItem {
  label: string;
  /** null pour la page courante */
  url: string | null;
}

export function useBreadcrumb(page: Ref<Page | null>): ComputedRef<BreadcrumbItem[]> {
  const context = useFrontContext();

  return computed(() => {
    const items: BreadcrumbItem[] = [
      { label: context.translate.main.breadcrumb, url: context.urls.home[context.datas.locale] ?? '/' },
    ];
    if (page.value !== null && !page.value.landingPage) {
      items.push({ label: page.value.title, url: null });
    }
    return items;
  });
}

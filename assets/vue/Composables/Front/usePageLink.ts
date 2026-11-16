/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Clic sur le lien d'une page : ouverte sans rechargement quand le contexte le permet, sauf clic modifié
 * (nouvel onglet, nouvelle fenêtre…)
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { PageSummary } from '@/ts/Front/Api/Page.type';

/**
 * @returns gestionnaire du clic, qui renvoie true quand la page est ouverte sans rechargement
 */
export function usePageLink(): (event: MouseEvent, summary: PageSummary) => boolean {
  const context = useFrontContext();

  return (event, summary) => {
    if (!context.canNavigate || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return false;
    }
    event.preventDefault();
    context.navigate(summary.category, summary.slug);
    return true;
  };
}

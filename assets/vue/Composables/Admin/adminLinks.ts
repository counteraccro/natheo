/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liens vers un écran de l'application ouverts sans rechargement (sidebar, « Nouveau », fil d'Ariane, contenus)
 */
import type { Router } from 'vue-router';
import { clickedLink } from '@/vue/Composables/Admin/clickedLink';
import { NOT_FOUND_ROUTE } from '@/vue/Composables/Admin/adminRouter';

/**
 * Crée l'écouteur de clics : un lien qui mène à une route d'un module passe par le routeur, les pages Twig rechargent
 * @param router
 * @param basePath /admin/<langue>/
 */
export function createAdminLinks(router: Router, basePath: string): (event: MouseEvent) => void {
  return (event: MouseEvent): void => {
    const link = clickedLink(event);
    if (link === null) return;
    const url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin || !url.pathname.startsWith(basePath)) return;
    const target = url.pathname.slice(basePath.length - 1) + url.search + url.hash;
    const route = router.resolve(target);
    if (route.name === undefined || route.name === NOT_FOUND_ROUTE || route.name === 'home') return;
    event.preventDefault();
    void router.push(target);
  };
}

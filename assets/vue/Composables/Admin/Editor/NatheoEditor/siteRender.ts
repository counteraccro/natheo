/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu du site dans Natheo-Editor : choix partagé par les éditeurs de l'écran et oublié au changement d'écran, rien
 * n'est enregistré ; feuille du thème du site chargée une seule fois
 */
import { ref, type Ref } from 'vue';
import type { Router } from 'vue-router';

const enabled = ref(false);
const watched = new WeakSet<Router>();
const loaded = new Set<string>();

/**
 * Choix du rendu du site, remis à zéro quand le routeur change d'écran
 * @param router routeur de l'application, null hors de l'application
 */
export function useSiteRender(router: Router | null): Ref<boolean> {
  if (router !== null && !watched.has(router)) {
    watched.add(router);
    router.afterEach((to, from) => {
      if (to.path !== from.path) enabled.value = false;
    });
  }
  return enabled;
}

/**
 * Ajoute la feuille du thème du site à la page, si elle n'y est pas déjà
 * @param href
 */
export function loadSiteStylesheet(href: string): void {
  if (loaded.has(href)) return;
  loaded.add(href);
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = href;
  document.head.append(link);
}

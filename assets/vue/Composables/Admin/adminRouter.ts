/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Routeur de l'application du back-office : routes des modules (écrans spécifiques du thème), page introuvable
 * et gardes (départ d'un formulaire, module visible, domaines chargés)
 */
import {
  createRouter,
  createWebHistory,
  type RouteLocationNormalized,
  type RouteLocationRaw,
  type Router,
  type RouteRecordRaw,
  type RouterHistory,
} from 'vue-router';
import { routesOf, type AdminModuleDefinition } from '@/vue/Composables/Admin/defineAdminModule';
import type { AdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import type { Translator } from '@/vue/Composables/Admin/useTranslate';
import type { AdminNotFoundProps, ThemeComponent } from '@/ts/Admin/Theme/AdminTheme.type';

export const NOT_FOUND_ROUTE = 'not-found';

export interface AdminRouterOptions {
  /** /admin/<langue>/ */
  basePath: string;
  modules: AdminModuleDefinition[];
  /** Environnement dev : outils de dev déclarés */
  debug: boolean;
  menu: AdminMenu;
  translator: Translator;
  /** Écran « Page introuvable » du thème */
  notFound: ThemeComponent;
  /** Écrans spécifiques redéfinis par le thème, par nom de route */
  pages?: Record<string, ThemeComponent>;
  /** Tableau de bord, page Twig tant qu'il n'est pas migré */
  dashboardUrl: string;
  /** Confirmation de départ d'un formulaire modifié */
  confirmLeave(): Promise<boolean>;
  /** Historique, en mémoire dans les tests */
  history?: RouterHistory;
  /** Ouverture d'une page Twig, remplacée dans les tests */
  assign?: (url: string) => void;
}

/**
 * Page introuvable à l'adresse demandée, sans changer l'URL
 * @param to
 */
function notFoundAt(to: RouteLocationNormalized): RouteLocationRaw {
  return {
    name: NOT_FOUND_ROUTE,
    params: { path: to.path.replace(/^\//, '').split('/') },
    query: to.query,
    hash: to.hash,
  };
}

/**
 * Routes dont le thème redéfinit l'écran, les autres gardent le composant de leur module
 * @param routes
 * @param pages écrans du thème par nom de route
 */
export function applyThemePages(
  routes: RouteRecordRaw[],
  pages: Record<string, ThemeComponent> = {}
): RouteRecordRaw[] {
  return routes.map((route) => {
    const page = typeof route.name === 'string' ? pages[route.name] : undefined;
    return page === undefined ? route : ({ ...route, component: page } as RouteRecordRaw);
  });
}

/**
 * Crée le routeur
 * @param options
 */
export function createAdminRouter(options: AdminRouterOptions): Router {
  const assign = options.assign ?? ((url: string) => window.location.assign(url));
  const router = createRouter({
    history: options.history ?? createWebHistory(options.basePath),
    routes: [
      {
        path: '/',
        name: 'home',
        component: options.notFound,
        beforeEnter: () => {
          assign(options.dashboardUrl);
          return false;
        },
      },
      ...applyThemePages(routesOf(options.modules, options.debug), options.pages),
      {
        path: '/:path(.*)*',
        name: NOT_FOUND_ROUTE,
        component: options.notFound,
        props: (route): AdminNotFoundProps => ({
          path: options.basePath.replace(/\/$/, '') + route.fullPath,
          dashboardUrl: options.dashboardUrl,
        }),
      },
    ],
  });

  router.beforeEach(async (to, from) => {
    // Premier affichage : aucun formulaire n'est encore ouvert
    if (from.matched.length > 0 && !(await options.confirmLeave())) {
      return false;
    }
    await options.menu.load();
    if (to.meta.module !== undefined && !options.menu.isModuleVisible(to.meta.module)) {
      return notFoundAt(to);
    }
    await Promise.all((to.meta.domains ?? []).map((domain) => options.translator.loadDomain(domain)));
    return true;
  });

  return router;
}

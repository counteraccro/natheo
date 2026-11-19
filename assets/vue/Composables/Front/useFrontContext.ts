/**
 * @author Gourdon Aymeric
 * @version 1.4
 * Contexte du site public partagé par les composables : props du thème, client API et cache des chargements
 */
import { getCurrentInstance, inject, ref, shallowRef, type InjectionKey, type Ref, type ShallowRef } from 'vue';
import { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { reloadOnExpiredToken } from '@/vue/Composables/Front/reloadOnExpiredToken';
import type { FrontThemeDatas, FrontThemeProps, FrontThemeUrls } from '@/ts/Front/FrontProps.type';
import type { FrontTranslate } from '@/ts/Front/FrontTranslate.type';
import type { Page } from '@/ts/Front/Api/Page.type';

export interface FrontContext {
  urls: FrontThemeUrls;
  datas: FrontThemeDatas;
  translate: FrontTranslate;
  api: ApiClient;
  /** Page affichée, renseignée par usePage() une fois chargée */
  page: ShallowRef<Page | null>;
  /** Slug de la page demandée, null pour l'accueil ; usePage() charge la page qui lui correspond */
  slug: Ref<string | null>;
  /** Navigation sans rechargement possible : ni prévisualisation, ni utilisateur connecté (lien d'édition calculé par le serveur) */
  canNavigate: boolean;
  /** Ouvre une page sans rechargement ; sans effet quand canNavigate est faux */
  navigate(category: string, slug: string): void;
  /** Chargement partagé : un seul appel par clé, quel que soit le nombre de composants qui le demandent */
  once<T>(key: string, loader: () => Promise<T>): Promise<T>;
  /** URL publique d'une page */
  pageUrl(category: string, slug: string, locale?: string): string;
  /** Message traduit d'une erreur de l'API */
  errorLabel(error: unknown): string;
}

const FRONT_CONTEXT_KEY: InjectionKey<FrontContext> = Symbol('natheo.front.context');

/**
 * Installe le contexte : appelé une seule fois, par le composant racine du thème
 */
export function createFrontContext(props: FrontThemeProps): FrontContext {
  const api = new ApiClient({
    baseUrl: props.urls.api,
    locale: props.datas.locale,
    token: () => props.datas.userToken || props.datas.apiToken,
    onUnauthorized: () => reloadOnExpiredToken(props.datas.userToken),
  });
  const loads = new Map<string, Promise<unknown>>();
  const home = (props.urls.home[props.datas.locale] ?? '/').replace(/\/?$/, '/');

  const context: FrontContext = {
    urls: props.urls,
    datas: props.datas,
    translate: props.translate,
    api,
    page: shallowRef<Page | null>(null),
    slug: ref(props.datas.slug),
    canNavigate: props.datas.preview === null && props.datas.user === null,
    navigate(category: string, slug: string): void {
      if (!context.canNavigate || slug === context.slug.value) {
        return;
      }
      window.history.pushState(null, '', context.pageUrl(category, slug));
      context.slug.value = slug;
    },
    once<T>(key: string, loader: () => Promise<T>): Promise<T> {
      if (!loads.has(key)) {
        // Un échec n'est pas gardé : la clé est rechargée au prochain appel
        loads.set(
          key,
          loader().catch((error: unknown) => {
            loads.delete(key);
            throw error;
          }),
        );
      }
      return loads.get(key) as Promise<T>;
    },
    pageUrl(category: string, slug: string, locale: string = props.datas.locale): string {
      const home = props.urls.home[locale] ?? props.urls.home[props.datas.locale] ?? '/';
      return home.replace(/\/?$/, '/') + category + '/' + slug;
    },
    errorLabel(error: unknown): string {
      const errors = props.translate.errorApi;
      if (props.datas.apiToken === '') {
        return errors.tokenMissing;
      }
      const status = error instanceof ApiError ? String(error.status) : 'default';
      return errors[status] ?? errors.default;
    },
  };

  if (context.canNavigate) {
    window.addEventListener('popstate', () => {
      context.slug.value = slugFromPath(window.location.pathname, home);
    });
  }

  // Fourni à l'application montée et non au composant : le composant racine l'utilise lui-même
  const app = getCurrentInstance()?.appContext.app;
  if (app === undefined) {
    throw new Error('createFrontContext() doit être appelé dans le setup du composant racine du thème');
  }
  app.provide(FRONT_CONTEXT_KEY, context);
  return context;
}

/**
 * Slug d'une adresse /<locale>/<catégorie>/<slug>, null pour l'accueil
 */
function slugFromPath(pathname: string, home: string): string | null {
  if (!pathname.startsWith(home)) {
    return null;
  }
  const segments = pathname.slice(home.length).split('/').filter((segment) => segment !== '');
  return segments[1] === undefined ? null : decodeURIComponent(segments[1]);
}

export function useFrontContext(): FrontContext {
  const context = inject(FRONT_CONTEXT_KEY);
  if (context === undefined) {
    throw new Error("useFrontContext() appelé hors d'un thème initialisé par createFrontContext()");
  }
  return context;
}

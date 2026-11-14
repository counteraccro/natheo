/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contexte du site public partagé par les composables : props du thème, client API et cache des chargements
 */
import { getCurrentInstance, inject, type InjectionKey } from 'vue';
import { ApiClient } from '@/vue/Composables/Front/Api/ApiClient';
import { ApiError } from '@/vue/Composables/Front/Api/ApiError';
import type { FrontThemeDatas, FrontThemeProps, FrontThemeUrls } from '@/ts/Front/FrontProps.type';
import type { FrontTranslate } from '@/ts/Front/FrontTranslate.type';

export interface FrontContext {
  urls: FrontThemeUrls;
  datas: FrontThemeDatas;
  translate: FrontTranslate;
  api: ApiClient;
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
    apiToken: props.datas.apiToken,
    userToken: props.datas.userToken,
    locale: props.datas.locale,
  });
  const loads = new Map<string, Promise<unknown>>();

  const context: FrontContext = {
    urls: props.urls,
    datas: props.datas,
    translate: props.translate,
    api,
    once<T>(key: string, loader: () => Promise<T>): Promise<T> {
      if (!loads.has(key)) {
        loads.set(key, loader());
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

  // Fourni à l'application montée et non au composant : le composant racine l'utilise lui-même
  const app = getCurrentInstance()?.appContext.app;
  if (app === undefined) {
    throw new Error('createFrontContext() doit être appelé dans le setup du composant racine du thème');
  }
  app.provide(FRONT_CONTEXT_KEY, context);
  return context;
}

export function useFrontContext(): FrontContext {
  const context = inject(FRONT_CONTEXT_KEY);
  if (context === undefined) {
    throw new Error("useFrontContext() appelé hors d'un thème initialisé par createFrontContext()");
  }
  return context;
}

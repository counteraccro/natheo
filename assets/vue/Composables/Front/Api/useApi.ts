/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Accès au client de l'API v2 installé par useFrontContext()
 */
import { inject, type InjectionKey } from 'vue';
import type { ApiClient } from '@/vue/Composables/Front/Api/ApiClient';

export const API_CLIENT_KEY: InjectionKey<ApiClient> = Symbol('natheo.front.api');

export function useApi(): ApiClient {
  const client = inject(API_CLIENT_KEY);
  if (client === undefined) {
    throw new Error("useApi() appelé hors d'un thème initialisé par useFrontContext()");
  }
  return client;
}

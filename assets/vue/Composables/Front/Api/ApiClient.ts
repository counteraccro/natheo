/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Client de l'API v2 du site public : seul point d'appel à l'API, commun à tous les thèmes
 */
import { ApiError } from '@/vue/Composables/Front/Api/ApiError';
import type { HydraCollection, Paginated } from '@/ts/Front/Api/Hydra.type';

export type ApiQuery = Record<string, string | number | boolean | null | undefined>;

export interface ApiClientOptions {
  /** Préfixe de l'API v2 (/api/v2) */
  baseUrl: string;
  apiToken: string;
  /** Access token de la session de l'utilisateur connecté, chaîne vide sinon */
  userToken: string;
  locale: string;
}

/** Marqueur du rechargement fait après l'expiration du token utilisateur */
const RELOAD_KEY = 'natheo.front.reload_on_401';

export class ApiClient {
  constructor(private readonly options: ApiClientOptions) {}

  get locale(): string {
    return this.options.locale;
  }

  /**
   * Ressource par son chemin (relatif au préfixe de l'API) ou son IRI
   */
  get<T>(path: string, query: ApiQuery = {}): Promise<T> {
    return this.request<T>('GET', path, query);
  }

  /**
   * Collection Hydra, avec sa position dans la pagination
   */
  async getCollection<T>(path: string, query: ApiQuery = {}): Promise<Paginated<T>> {
    const url = this.buildUrl(path, query);
    const collection = await this.request<HydraCollection<T>>('GET', url);
    const page = ApiClient.pageOf(url) ?? 1;
    const lastPage = collection.view?.last ? (ApiClient.pageOf(collection.view.last) ?? page) : page;
    return { members: collection.member, total: collection.totalItems, page, lastPage };
  }

  post<T>(path: string, body: object): Promise<T> {
    return this.request<T>('POST', path, {}, body, 'application/ld+json');
  }

  patch<T>(path: string, body: object): Promise<T> {
    return this.request<T>('PATCH', path, {}, body, 'application/merge-patch+json');
  }

  private async request<T>(
    method: string,
    path: string,
    query: ApiQuery = {},
    body?: object,
    contentType?: string
  ): Promise<T> {
    const headers: Record<string, string> = {
      Accept: 'application/ld+json',
      Authorization: 'Bearer ' + (this.options.userToken || this.options.apiToken),
    };
    if (contentType) {
      headers['Content-Type'] = contentType;
    }

    let response: Response;
    try {
      response = await fetch(this.buildUrl(path, query), {
        method,
        headers,
        body: body === undefined ? undefined : JSON.stringify(body),
      });
    } catch (error) {
      throw ApiError.fromNetwork(error);
    }

    if (!response.ok) {
      if (response.status === 401) {
        this.reloadOnExpiredSession();
      }
      throw await ApiError.fromResponse(response);
    }

    sessionStorage.removeItem(RELOAD_KEY);
    return response.status === 204 ? (undefined as T) : ((await response.json()) as T);
  }

  /**
   * Token utilisateur expiré : la page est rechargée une fois pour que le serveur le renouvelle
   */
  private reloadOnExpiredSession(): void {
    if (this.options.userToken === '' || sessionStorage.getItem(RELOAD_KEY) !== null) {
      sessionStorage.removeItem(RELOAD_KEY);
      return;
    }
    sessionStorage.setItem(RELOAD_KEY, '1');
    window.location.reload();
  }

  /**
   * URL complète : chemin préfixé sauf IRI ou URL déjà renvoyée par l'API, locale ajoutée si absente
   */
  private buildUrl(path: string, query: ApiQuery = {}): string {
    const base = this.options.baseUrl.replace(/\/$/, '');
    const url = new URL(path.startsWith(base + '/') ? path : base + path, window.location.origin);
    if (!url.searchParams.has('locale')) {
      url.searchParams.set('locale', this.options.locale);
    }
    for (const [key, value] of Object.entries(query)) {
      if (value !== null && value !== undefined && value !== '') {
        url.searchParams.set(key, String(value));
      }
    }
    return url.pathname + url.search;
  }

  private static pageOf(url: string): number | null {
    const page = new URL(url, window.location.origin).searchParams.get('page');
    return page === null ? null : Number(page);
  }
}

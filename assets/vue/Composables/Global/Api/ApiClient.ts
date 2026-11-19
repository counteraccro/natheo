/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Client de l'API v2, commun au site public et au back-office
 */
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import type { ApiResource, HydraCollection, Paginated } from '@/ts/Global/Api/Hydra.type';

export type ApiQuery = Record<string, string | number | boolean | null | undefined>;

export interface ApiClientOptions {
  /** Préfixe de l'API v2 (/api/v2) */
  baseUrl: string;
  locale: string;
  /** Token Bearer de la requête, relu à chaque appel */
  token: () => string | Promise<string>;
  /** Appelé sur un 401 : la requête est rejouée une fois s'il renvoie true */
  onUnauthorized?: () => boolean | Promise<boolean>;
}

export interface ApiWriteOptions {
  /** ETag lu avec la ressource, envoyé dans If-Match (412 si elle a changé depuis) */
  ifMatch?: string | null;
}

interface ApiRequest {
  method: string;
  url: string;
  body?: object;
  contentType?: string;
  ifMatch?: string | null;
}

export class ApiClient {
  constructor(private readonly options: ApiClientOptions) {}

  get locale(): string {
    return this.options.locale;
  }

  /**
   * Ressource par son chemin (relatif au préfixe de l'API) ou son IRI
   */
  async get<T>(path: string, query: ApiQuery = {}): Promise<T> {
    return (await this.getResource<T>(path, query)).data;
  }

  /**
   * Ressource avec son ETag, à renvoyer dans If-Match pour la modifier
   */
  async getResource<T>(path: string, query: ApiQuery = {}): Promise<ApiResource<T>> {
    const response = await this.send({ method: 'GET', url: this.buildUrl(path, query) });
    return { data: (await response.json()) as T, etag: response.headers.get('ETag') };
  }

  /**
   * Collection Hydra, avec sa position dans la pagination
   */
  async getCollection<T>(path: string, query: ApiQuery = {}): Promise<Paginated<T>> {
    const url = this.buildUrl(path, query);
    const collection = (await (await this.send({ method: 'GET', url })).json()) as HydraCollection<T>;
    const page = ApiClient.pageOf(url) ?? 1;
    const lastPage = collection.view?.last ? (ApiClient.pageOf(collection.view.last) ?? page) : page;
    return { members: collection.member, total: collection.totalItems, page, lastPage };
  }

  post<T>(path: string, body: object): Promise<T> {
    return this.write<T>({ method: 'POST', url: this.buildUrl(path), body, contentType: 'application/ld+json' });
  }

  patch<T>(path: string, body: object, options: ApiWriteOptions = {}): Promise<T> {
    return this.write<T>({
      method: 'PATCH',
      url: this.buildUrl(path),
      body,
      contentType: 'application/merge-patch+json',
      ifMatch: options.ifMatch,
    });
  }

  async delete(path: string, options: ApiWriteOptions = {}): Promise<void> {
    await this.send({ method: 'DELETE', url: this.buildUrl(path), ifMatch: options.ifMatch });
  }

  private async write<T>(request: ApiRequest): Promise<T> {
    const response = await this.send(request);
    return response.status === 204 ? (undefined as T) : ((await response.json()) as T);
  }

  /**
   * Envoie la requête, rejouée une fois après un 401 si onUnauthorized le permet
   */
  private async send(request: ApiRequest, retried: boolean = false): Promise<Response> {
    const headers: Record<string, string> = {
      Accept: 'application/ld+json',
      Authorization: 'Bearer ' + (await this.options.token()),
    };
    if (request.contentType) {
      headers['Content-Type'] = request.contentType;
    }
    if (request.ifMatch) {
      headers['If-Match'] = request.ifMatch;
    }

    let response: Response;
    try {
      response = await fetch(request.url, {
        method: request.method,
        headers,
        body: request.body === undefined ? undefined : JSON.stringify(request.body),
      });
    } catch (error) {
      throw ApiError.fromNetwork(error);
    }

    if (response.status === 401 && !retried && this.options.onUnauthorized && (await this.options.onUnauthorized())) {
      return this.send(request, true);
    }
    if (!response.ok) {
      throw await ApiError.fromResponse(response);
    }
    return response;
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

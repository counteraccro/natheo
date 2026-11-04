/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Appels à l'API depuis le front. Chaque méthode renvoie une promesse rejetée avec un FrontApiError en cas d'échec
 */
import axios, { type AxiosRequestConfig } from 'axios';
import type {
  FrontApiError,
  FrontOptionsSystem,
  FrontPageResponse,
  FrontUrls,
} from '@/ts/Front/NatheoHorizon/Structure.type';
import type {
  CommentsResponse,
  ModerateCommentPayload,
  NewCommentPayload,
} from '@/ts/Front/NatheoHorizon/ContentComment.type';

type QueryParams = Record<string, string | number | null | undefined>;

interface ApiErrorBody {
  code_http?: number;
  errors?: string[];
}

export class FrontApi {
  constructor(
    private readonly urls: FrontUrls,
    private readonly apiToken: string,
    private readonly userToken: string
  ) {}

  getOptionSystems(): Promise<FrontOptionsSystem> {
    return this.request<FrontOptionsSystem>({ method: 'get', url: this.urls.apiOptionsSystems });
  }

  getPageBySlug(params: QueryParams): Promise<FrontPageResponse> {
    return this.request<FrontPageResponse>({ method: 'get', url: this.urls.apiPageFind, params: this.clean(params) });
  }

  getContentPage<T>(params: QueryParams): Promise<T> {
    return this.request<T>({ method: 'get', url: this.urls.apiPageContent, params: this.clean(params) });
  }

  getCommentByPage(params: QueryParams): Promise<CommentsResponse> {
    return this.request<CommentsResponse>({
      method: 'get',
      url: this.urls.apiCommentsByPage,
      params: this.clean(params),
    });
  }

  addComment(data: NewCommentPayload): Promise<{ id: number }> {
    return this.request<{ id: number }>({ method: 'post', url: this.urls.apiAddComment, data });
  }

  moderateComment(id: number, data: ModerateCommentPayload): Promise<unknown> {
    // L'URL est générée côté serveur avec l'id 0 comme marqueur
    const url = this.urls.apiModerateComment.replace(/\/0$/, '/' + id);
    return this.request<unknown>({ method: 'put', url, data });
  }

  private request<T>(config: AxiosRequestConfig): Promise<T> {
    const headers: Record<string, string> = { Authorization: 'Bearer ' + this.apiToken };
    if (this.userToken !== '') {
      headers['User-Token'] = this.userToken;
    }

    return axios
      .request<{ data: T }>({ ...config, headers })
      .then((response) => response.data.data)
      .catch((error: unknown) => {
        throw FrontApi.toApiError(error);
      });
  }

  /**
   * Retire les paramètres vides pour ne pas les envoyer dans la query string
   */
  private clean(params: QueryParams): QueryParams {
    return Object.fromEntries(
      Object.entries(params).filter(([, value]) => value !== null && value !== undefined && value !== '')
    );
  }

  /**
   * Normalise une erreur axios (réponse API, timeout, réseau...) en FrontApiError
   */
  static toApiError(error: unknown): FrontApiError {
    if (axios.isAxiosError<ApiErrorBody>(error) && error.response) {
      return {
        code: error.response.data?.code_http ?? error.response.status,
        message: error.response.data?.errors?.[0] ?? error.message,
      };
    }
    return { code: 0, message: error instanceof Error ? error.message : String(error) };
  }
}

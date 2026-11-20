/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Erreur d'un appel à l'API v2, lue depuis la réponse application/problem+json
 */
import type { Problem } from '@/ts/Global/Api/Problem.type';

export class ApiError extends Error {
  constructor(
    readonly status: number,
    readonly title: string,
    readonly detail: string | null = null,
    /** Corps JSON de la réponse (violations d'un 422…), null s'il est absent */
    readonly body: unknown = null
  ) {
    super(detail ?? title);
    this.name = 'ApiError';
  }

  /**
   * Erreur construite depuis une réponse en échec, problem+json ou non
   */
  static async fromResponse(response: Response): Promise<ApiError> {
    let body: unknown = null;
    try {
      body = await response.json();
    } catch {
      // Corps absent ou non JSON : seul le statut HTTP est connu
    }
    const problem = (body ?? {}) as Partial<Problem>;
    return new ApiError(response.status, problem.title ?? response.statusText, problem.detail ?? null, body);
  }

  /**
   * Erreur réseau ou requête interrompue, sans réponse de l'API
   */
  static fromNetwork(error: unknown): ApiError {
    return new ApiError(0, error instanceof Error ? error.message : String(error));
  }
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Erreur de l'API v2 au format application/problem+json
 */

export interface Problem {
  type?: string;
  title: string;
  status: number;
  detail?: string;
}

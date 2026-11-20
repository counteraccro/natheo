/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource Info de l'API v2 : nom du site et versions
 */
import type { JsonLdResource } from '@/ts/Global/Api/Hydra.type';

export interface Info extends JsonLdResource {
  siteName: string;
  natheoVersion: string;
  apiVersion: string;
  /** Données ajoutées par une surcharge du provider, null sans surcharge */
  extra: Record<string, unknown> | null;
}

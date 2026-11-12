/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types du format JSON-LD / Hydra de l'API v2
 */

/** Identité JSON-LD d'une ressource ou d'un objet embarqué qui a son IRI */
export interface JsonLdResource {
  '@id': string;
  '@type': string;
}

/** Liens de pagination d'une collection */
export interface HydraView {
  '@id': string;
  first?: string;
  last?: string;
  previous?: string;
  next?: string;
}

/** Collection Hydra telle que renvoyée par l'API */
export interface HydraCollection<T> {
  member: T[];
  totalItems: number;
  view?: HydraView;
}

/** Collection lue par le client : membres et position dans la pagination */
export interface Paginated<T> {
  members: T[];
  total: number;
  page: number;
  lastPage: number;
}

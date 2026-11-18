/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Morceau d'une requête SQL colorée
 */
export interface SqlToken {
  text: string;
  kind: 'keyword' | 'string' | 'number' | 'identifier' | 'plain';
}

/** Morceau d'un texte, surligné quand il correspond à la recherche */
export interface TextSegment {
  text: string;
  match: boolean;
}

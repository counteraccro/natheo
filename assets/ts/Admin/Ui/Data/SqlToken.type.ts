/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Morceau d'une requête SQL colorée
 */
export interface SqlToken {
  text: string;
  kind: 'keyword' | 'string' | 'number' | 'identifier' | 'comment' | 'plain';
}

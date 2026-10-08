/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant GridPaginate
 */

export interface GridPaginateTranslate {
  page: string;
  on: string;
  row: string;
}

/**
 * Élément de pagination : numéro de page ou séparateur "..."
 */
export type GridPaginatePage = number | '...';

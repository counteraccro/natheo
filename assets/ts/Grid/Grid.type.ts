/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant Grid
 */

/**
 * Action affichée dans la colonne "action" d'une ligne du grid
 */
export interface GridAction {
  label: string[];
  color: string;
  url: string;
  ajax: boolean;
  id?: number;
  type?: string;
  confirm?: boolean;
  msgConfirm?: string;
}

/**
 * Ligne du grid : une valeur (HTML) par colonne, plus la colonne "action"
 */
export interface GridRow {
  [column: string]: unknown;
  action?: GridAction[];
  isDisabled?: boolean;
}

/**
 * Ordre de tri par colonne (1 : ASC, -1 : DESC)
 */
export type GridSortOrders = Record<string, number>;

export interface GridTranslate {
  noresult: string;
}

export type GridSearchMode = 'table' | 'bdd';

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant GenericGrid
 */
import type { GridRow, GridTranslate } from '@/ts/Grid/Grid.type';
import type { GridPaginateTranslate } from '@/ts/Grid/GridPaginate.type';

export interface GenericGridTranslate {
  placeholder: string;
  placeholderBddSearch: string;
  placeholderTableSearch: string;
  textBddSearch: string;
  titleSearch: string;
  textTableSearch: string;
  textShowQuery: string;
  textHideQuery: string;
  textShowTrieOption: string;
  textHideTrieOption: string;
  btnSearch: string;
  loading: string;
  titleSuccess: string;
  titleError: string;
  time: string;
  confirmTitle: string;
  confirmText: string;
  confirmBtnOK: string;
  confirmBtnNo: string;
  copySuccess: string;
  copyError: string;
  queryTitle: string;
  filterOnlyMe: string;
  filterAll: string;
  titleTrieOption: string;
  titleTrieOptionSubMenu: string;
  trieOptionListeField: string;
  trieOptionListeOrder: string;
  trieOptionBtn: string;
}

/**
 * Réponse JSON des routes "load-grid-data" (GridService::addAllDataRequiredGrid)
 */
export interface GenericGridResponse {
  column: string[];
  data: GridRow[];
  nb: number;
  listLimit: Record<string, number>;
  translate: {
    genericGrid: GenericGridTranslate;
    gridPaginate: GridPaginateTranslate;
    grid: GridTranslate;
  };
  urlSaveSql?: string;
  listOrderField?: Record<string, string>;
  sql?: string;
}

/**
 * Réponse ajax d'une action du grid ("type" : ancien format de retour)
 */
export interface GenericGridActionResponse {
  success?: boolean;
  type?: string;
  msg: string;
}

export type GenericGridHttpType = 'get' | 'post' | 'put' | 'patch' | 'delete';
export type GenericGridFilter = 'all' | 'me';
export type GenericGridOrder = 'ASC' | 'DESC';

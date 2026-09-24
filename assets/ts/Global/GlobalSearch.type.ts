/**
 * Types pour le composant GlobalSearch
 * @author Gourdon Aymeric
 * @version 1.0
 */

export type GlobalSearchEntity = 'page' | 'menu' | 'faq' | 'tag' | 'user';

export interface GlobalSearchUrls {
  listingPage: string;
  searchPage: string;
}

export interface GlobalSearchTranslateOnglet {
  onglet: string;
  title: string;
  description: string;
  noResult: string;
  create: string;
  update: string;
  author: string;
  loading: string;
  content?: string;
  noResultContent?: string;
}

export interface GlobalSearchTranslatePaginate {
  page: string;
  on: string;
  row: string;
}

export interface GlobalSearchTranslate {
  subTitlePage: string;
  totalResult: string;
  totalNoResult: string;
  paginate: GlobalSearchTranslatePaginate;
  ongletPage: GlobalSearchTranslateOnglet;
  ongletMenu: GlobalSearchTranslateOnglet;
  ongletFaq: GlobalSearchTranslateOnglet;
  ongletTag: GlobalSearchTranslateOnglet;
  ongletUser: GlobalSearchTranslateOnglet;
}

export interface GlobalSearchResultElement {
  id: number;
  label: string;
  img?: string | null;
  contents: string[];
  date: {
    create: string;
    update: string;
  };
  author?: string;
  urls: {
    edit: string;
    preview?: string;
  };
}

export interface GlobalSearchResult {
  elements: GlobalSearchResultElement[];
  total: number;
  error?: string;
}

export interface GlobalSearchPaginate {
  current: number;
  limit: number;
}

export interface GlobalSearchResponse {
  'recherche page': string;
  result: GlobalSearchResult;
  paginate: GlobalSearchPaginate;
}

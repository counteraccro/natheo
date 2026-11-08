/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant BlockPagesPendingValidation
 */

export interface BlockPagesPendingValidationUrls {
  load_block_dashboard: string;
  url_pages: string;
}

export interface BlockPagesPendingValidationTranslate {
  title: string;
  link_page: string;
  column_id: string;
  column_title: string;
  column_author: string;
  column_date: string;
  column_action: string;
  link_edit: string;
  empty: string;
}

export interface BlockPagesPendingValidationDatas {
  active: boolean;
}

export interface LoadBlockDashboardResponse {
  success: boolean;
  body: PendingPage[] | null;
  error: string | null;
}

export interface PendingPage {
  id: number;
  title: string;
  author: string;
  date: string;
  url: string;
}

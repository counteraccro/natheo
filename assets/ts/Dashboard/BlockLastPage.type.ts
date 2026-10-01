/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant BlockLastPage
 */

export interface BlockLastPageUrls {
  load_block_dashboard: string;
  url_pages: string;
}

export interface BlockLastPageTranslate {
  link_page: string;
  title: string;
  table_id: string;
  table_title: string;
  table_status: string;
  table_date: string;
}

export interface LoadBlockDashboardResponse {
  success: boolean;
  body: Page[] | null;
  error: string | null;
}

export interface Page {
  id: number;
  title: string;
  status: string;
  date: string;
}

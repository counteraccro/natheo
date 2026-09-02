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
}

export interface LoadBlockDashboardResponse {
  success: boolean;
  body: string | null;
  error: string | null;
}

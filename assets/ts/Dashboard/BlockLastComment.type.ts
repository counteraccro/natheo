/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant BlockLastComment
 */

export interface BlockLastCommentUrls {
  load_block_dashboard: string;
  url_comments: string;
}

export interface BlockLastCommentTranslate {
  title: string;
  loading: string;
  table_id: string;
  table_author: string;
  table_status: string;
  table_date: string;
  link_comment: string;
}

export interface Comment {
  id: number;
  author: string;
  status: string;
  date: string;
}

export interface LoadBlockDashboardResponse {
  success: boolean;
  body: Comment[] | null;
  error: string | null;
}

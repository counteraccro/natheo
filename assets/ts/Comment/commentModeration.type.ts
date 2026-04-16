/**
 * Types pour le composant CommentModeration
 * @author Gourdon Aymeric
 * @version 1.0
 */

export interface CommentModerationUrls {
  filter: string;
  update: string;
}

export interface CommentModerationTranslate {
  loading: string;
  legend_search: string;
  status_label: string;
  status_default: string;
  pages_label: string;
  pages_default: string;
  btn_reset: string;
  selection_title: string;
  selection_comment: string;
  selection_status: string;
  selection_comment_moderation: string;
  selection_submit: string;
  comment_id: string;
  comment_date: string;
  comment_author: string;
  comment_comment: string;
  comment_page: string;
  comment_info: string;
  comment_ip: string;
  comment_user_agent: string;
  comment_moderator: string;
  comment_update: string;
  no_result: string;
  toast_title_success: string;
  toast_title_error: string;
  toast_time: string;
  paginate: Record<string, string>;
  comment_unselect_all: string;
  comment_select_all: string;
  statistiques: string;
  status_short_validate: string;
  status_short_moderate: string;
  status_short_waiting: string;
}

export interface CommentModerationDatas {
  defaultStatus: string;
  status: Record<string, string>;
  pages: Record<string, string>;
  page: number;
  limit: string | number;
}

export interface Comment {
  id: number;
  date: string;
  author: string;
  status: string;
  page: string;
  comment: string;
  ip: string;
  userAgent: string;
  moderator: string | null;
  update: string;
  commentModeration: string;
  email: string;
}

export interface CommentListResult {
  data: Comment[];
  nb: number;
}

export interface ModerationState {
  selected: number[];
  status: string;
  moderateComment: string;
}

export interface FiltersState {
  status: string;
  pages: string;
}

export interface ToastState {
  show: boolean;
  msg: string;
}

export interface ToastsState {
  toastSuccess: ToastState;
  toastError: ToastState;
}

export interface UpdateModerationResponse {
  success: boolean;
  msg: string;
}

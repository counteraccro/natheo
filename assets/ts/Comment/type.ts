/**
 * Interfaces TypeScript pour le composant CommentEdit
 * @author Gourdon Aymeric
 * @version 1.0
 */

/** Représente un utilisateur de modération */
export interface UserModeration {
  login: string;
}

export interface Page {
  title: string;
  url: string;
  createdAt: string;
}

/** Représente un commentaire */
export interface Comment {
  id: number;
  comment: string;
  status: string;
  statusStr: string;
  moderationComment: string;
  userModeration: UserModeration | null;
  author: string;
  email: string;
  createdAt: string;
  ip: string;
  userAgent: string;
  page: Page;
}

/** Réponse de l'API pour le chargement d'un commentaire */
export interface LoadCommentResponse {
  comment: Comment;
}

/** Réponse de l'API pour la sauvegarde d'un commentaire */
export interface SaveCommentResponse {
  success: boolean;
  msg: string;
}

/** Structure d'un toast */
export interface ToastState {
  show: boolean;
  msg: string;
}

/** Ensemble des toasts du composant */
export interface Toasts {
  success: ToastState;
  error: ToastState;
}

/** URLs passées en props */
export interface Urls {
  load_comment: string;
  save: string;
  index: string;
}

/** Données passées en props */
export interface Datas {
  id: number;
  status: Record<string, string>;
  statusModerate: string;
}

/** Traductions passées en props */
export interface Translate {
  loading: string;
  titleInfo: string;
  moderationComment: string;
  moderationAuthor: string;
  author: string;
  created: string;
  ip: string;
  userAgent: string;
  btnEdit: string;
  toast_title_success: string;
  toast_title_error: string;
  toast_time: string;
  markdown: Record<string, Record<string, string>>;
  commentTitle: string;
  cancel: string;
  status: string;
  status_actuel: string;
  status_label: string;
  page_associated: string;
  page_created: string;
  page_link: string;
}

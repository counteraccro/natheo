/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressources AdminComment (GET /admin/comments) et AdminCommentStatusCount (GET /admin/comment_status_counts) de
 * l'API v2
 */
import type { ScreenAuthor } from '@/ts/Admin/Screen/Screen.type';

/** Statut de modération, slug de CommentStatus */
export type CommentStatusSlug = 'wait_validation' | 'validate' | 'moderate';

/** Page du commentaire, titre dans la langue de contenu */
export interface AdminCommentPage {
  id: number;
  title: string;
  /** Adresse publique, lecture d'un élément seulement */
  url: string | null;
}

export interface AdminComment {
  id: number;
  /** Texte en Markdown, lecture d'un élément seulement */
  comment: string;
  /** Texte sans Markdown, coupé, liste seulement */
  excerpt: string;
  author: string;
  /** Lecture d'un élément seulement */
  email: string | null;
  ip: string;
  userAgent: string;
  status: CommentStatusSlug;
  /** Lecture d'un élément seulement */
  moderationComment: string | null;
  moderator: ScreenAuthor | null;
  page: AdminCommentPage | null;
  createdAt: string | null;
  updatedAt: string | null;
  permissions: { edit: boolean; disable: boolean; delete: boolean };
}

export interface AdminCommentStatusCount {
  status: CommentStatusSlug;
  count: number;
}

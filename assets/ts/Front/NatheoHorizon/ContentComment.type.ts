/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant ContentComment
 */

export interface FrontComment {
  id: number;
  author: string;
  status: number;
  createdAt: number;
  updateAt: number;
  comment: string;
  moderate?: string | null;
}

export interface CommentsResponse {
  comments: FrontComment[];
  current_page: number;
  rows: number;
  limit: number;
}

export interface NewCommentPayload {
  page_slug: string;
  author: string;
  email: string;
  comment: string;
  user_agent: string;
}

export interface ModerateCommentPayload {
  status: number;
  moderation_comment: string;
}

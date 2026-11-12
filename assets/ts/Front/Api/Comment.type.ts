/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource Comment de l'API v2
 */
import type { JsonLdResource } from '@/ts/Front/Api/Hydra.type';

export type CommentStatus = 'wait_validation' | 'validate' | 'moderate';

export interface Comment extends JsonLdResource {
  id: number;
  /** IRI de la page commentée */
  page: string | null;
  status: CommentStatus;
  author: string | null;
  comment: string | null;
  moderationComment: string | null;
  createdAt: string | null;
  updatedAt: string | null;
}

/** Corps de POST /comments */
export interface NewComment {
  page: string;
  author: string;
  email?: string | null;
  comment: string;
  userAgent?: string | null;
}

/** Corps de PATCH /comments/{id} (merge-patch) */
export interface ModerateComment {
  status?: CommentStatus;
  moderationComment?: string | null;
}

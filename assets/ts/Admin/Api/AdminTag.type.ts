/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource AdminTag de l'API v2 (GET /admin/tags)
 */
import type { ScreenAuthor } from '@/ts/Admin/Screen/Screen.type';

export interface AdminTagTranslation {
  locale: string;
  label: string;
}

export interface AdminTag {
  id: number;
  /** Libellé dans la langue de contenu */
  label: string;
  color: string;
  disabled: boolean;
  /** Lecture d'un élément seulement */
  translations: AdminTagTranslation[];
  pageCount: number;
  author: ScreenAuthor | null;
  createdAt: string | null;
  updatedAt: string | null;
  permissions: { edit: boolean; disable: boolean; delete: boolean };
}

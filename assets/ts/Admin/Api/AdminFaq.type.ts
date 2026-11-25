/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressources AdminFaq, AdminFaqCategory et AdminFaqQuestion de l'API v2 (GET /admin/faqs)
 */
import type { ScreenAuthor } from '@/ts/Admin/Screen/Screen.type';

export interface AdminFaqTitleTranslation {
  locale: string;
  title: string;
}

export interface AdminFaqQuestionTranslation {
  locale: string;
  title: string;
  /** Markdown */
  answer: string;
}

export interface AdminFaqPermissions {
  edit: boolean;
  disable: boolean;
  delete: boolean;
}

/** Question de l'arbre d'une FAQ, sans sa réponse */
export interface AdminFaqTreeQuestion {
  '@id': string;
  id: number;
  position: number;
  disabled: boolean;
  /** Titre dans la langue de contenu */
  title: string;
}

/** Catégorie de l'arbre d'une FAQ */
export interface AdminFaqTreeCategory {
  '@id': string;
  id: number;
  position: number;
  disabled: boolean;
  title: string;
  translations: AdminFaqTitleTranslation[];
  questions: AdminFaqTreeQuestion[];
}

export interface AdminFaq {
  id: number;
  /** Titre dans la langue de contenu */
  title: string;
  disabled: boolean;
  /** Lecture d'un élément seulement */
  translations: AdminFaqTitleTranslation[];
  categoryCount: number;
  questionCount: number;
  pageCount: number;
  author: ScreenAuthor | null;
  createdAt: string | null;
  updatedAt: string | null;
  permissions: AdminFaqPermissions;
  /** Lecture d'un élément seulement */
  categories?: AdminFaqTreeCategory[];
}

/** Catégorie lue ou écrite seule (GET /admin/faq_categories/{id}) */
export interface AdminFaqCategory {
  id?: number;
  /** IRI de la FAQ */
  faq: string;
  position?: number;
  disabled: boolean;
  title?: string;
  translations: AdminFaqTitleTranslation[];
  permissions?: AdminFaqPermissions;
}

/** Question lue ou écrite seule (GET /admin/faq_questions/{id}) */
export interface AdminFaqQuestion {
  id?: number;
  faq?: string;
  /** IRI de la catégorie */
  category: string;
  position?: number;
  disabled: boolean;
  title?: string;
  translations: AdminFaqQuestionTranslation[];
  permissions?: AdminFaqPermissions;
}

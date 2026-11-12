/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource PageContent de l'API v2 : un bloc de contenu d'une page
 */
import type { JsonLdResource } from '@/ts/Front/Api/Hydra.type';
import type { PageCategory } from '@/ts/Front/Api/Page.type';

export type PageContentType = 'text' | 'faq' | 'listing';

export interface TextContent {
  html: string;
}

export interface FaqContentQuestion {
  title: string;
  answer: string;
}

export interface FaqContentCategory {
  title: string;
  questions: FaqContentQuestion[];
}

export interface FaqContent {
  title: string;
  categories: FaqContentCategory[];
}

export interface ListingContent {
  category: PageCategory;
  title: string;
  /** URL de la collection des pages de la catégorie */
  pages: string;
}

/** Contenu d'un bloc selon son type */
export interface PageContentByType {
  text: TextContent;
  faq: FaqContent;
  listing: ListingContent;
}

/** Bloc tel qu'il figure dans une page (groupe page:read) */
export interface PageContentSummary extends JsonLdResource {
  id: number;
  type: PageContentType;
  position: number;
}

/** Bloc complet (groupe page_content:read) */
export interface PageContent<T extends PageContentType = PageContentType> extends PageContentSummary {
  type: T;
  content: PageContentByType[T] | null;
}

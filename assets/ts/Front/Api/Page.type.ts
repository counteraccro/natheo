/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource Page de l'API v2
 */
import type { JsonLdResource } from '@/ts/Front/Api/Hydra.type';
import type { Menu } from '@/ts/Front/Api/Menu.type';
import type { PageContentSummary } from '@/ts/Front/Api/PageContent.type';

/** Disposition des blocs d'une page */
export type PageRender =
  | 'one_block'
  | 'two_block'
  | 'three_block'
  | 'two_block_bottom'
  | 'three_block_bottom'
  | 'one_two_block'
  | 'two_one_block'
  | 'two_two_block';

export type PageCategory =
  'page' | 'article' | 'projet' | 'blog' | 'evenement' | 'news' | 'evolution' | 'documentation' | 'faq';

export interface Author {
  name: string;
  description: string | null;
  avatar: string | null;
}

export interface Tag {
  id: number;
  label: string;
  color: string;
}

export interface Meta {
  name: string;
  value: string;
}

/** Page dans une collection (groupe page:list) */
export interface PageSummary extends JsonLdResource {
  id: number;
  slug: string;
  title: string;
  category: PageCategory;
  headerImage: string | null;
  author: Author | null;
  tags: Tag[];
  createdAt: string | null;
  updatedAt: string | null;
}

/** Page complète (groupe page:read) */
export interface Page extends PageSummary {
  render: PageRender;
  landingPage: boolean;
  openComment: boolean;
  reads: number;
  seo: Meta[];
  contents: PageContentSummary[];
  menus: Menu[];
}

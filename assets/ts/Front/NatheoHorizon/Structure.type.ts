/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant Structure du front Natheo Horizon et les données partagées entre ses composants
 */

export interface FrontUrls {
  apiPageFind: string;
  apiOptionsSystems: string;
  apiPageContent: string;
  adminAuth: string;
  sitemap: string;
  logout: string;
  indexFr: string;
  indexEs: string;
  indexEn: string;
  apiCommentsByPage: string;
  apiAddComment: string;
  apiModerateComment: string;
}

export interface FrontUserInfo {
  login: string;
  avatarImg: string | null;
  avatar: string;
  canModerate: boolean;
}

export interface FrontDatas {
  slug: string | null;
  locale: string;
  apiToken: string;
  pageCategories: Record<number, string>;
  pathAvatar: string;
  basePath: string;
  userToken: string;
  userInfo: FrontUserInfo | null;
  preview: boolean;
}

export type {
  FrontPaginationTranslate,
  FrontCommentTranslate,
  FrontArticleFooterTranslate,
  FrontListingTranslate,
  FrontMainTranslate,
  FrontHeaderTranslate,
  FrontFooterTranslate,
  FrontErrorApiTranslate,
  FrontTranslate,
} from '@/ts/Front/FrontTranslate.type';

/** Options systèmes exposées par l'API (liste blanche ApiOptionSystemService) */
export interface FrontOptionsSystem {
  OS_SITE_NAME: string;
  OS_FRONT_SCRIPT_TOP: string;
  OS_FRONT_SCRIPT_START_BODY: string;
  OS_FRONT_SCRIPT_END_BODY: string;
  OS_OPEN_COMMENT: string;
  OS_ADRESSE_SITE: string;
  OS_THEME_FRONT_SITE: string;
  OS_FRONT_FOOTER_TEXTE: string;
  OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL: string;
  OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL: string;
  OS_FRONT_FOOTER_SOCIAL_GITHUB_URL: string;
  OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL: string;
  OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL: string;
  OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL: string;
  OS_FRONT_FOOTER_SOCIAL_X_URL: string;
  OS_FRONT_ROBOT_NO_INDEX: string;
  OS_FRONT_ROBOT_NO_FOLLOW: string;
}

/** Élément pouvant générer un lien (élément de menu, page d'un listing) */
export interface FrontLinkable {
  url?: string;
  slug?: string;
  category?: number | string;
}

export interface FrontMenuElement extends FrontLinkable {
  id: number;
  target: string;
  label: string;
  columnPosition: number;
  rowPosition: number;
  elements?: FrontMenuElement[];
}

export interface FrontMenu {
  id: number;
  position: string;
  type: number;
  elements: FrontMenuElement[];
}

export interface FrontPageMenus {
  HEADER?: FrontMenu;
  FOOTER?: FrontMenu;
  LEFT?: FrontMenu;
  RIGHT?: FrontMenu;
}

export interface FrontPageContent {
  id: number;
  type: number;
  position: number;
}

export interface FrontPageTag {
  label: string;
  color: string;
}

export interface FrontPageAuthor {
  author: string;
  description: string | null;
  avatar: string | null;
}

export interface FrontPage {
  title: string;
  slug: string;
  status: number;
  render: number;
  author: FrontPageAuthor;
  created: number;
  update: number;
  headerImg: string | null;
  tags?: FrontPageTag[];
  statistiques?: Record<string, string>;
  contents: FrontPageContent[];
  openComment: boolean;
  menus?: FrontPageMenus;
  /** Rendu des contenus d'un brouillon prévisualisé, indexé par leur id négatif */
  preview_contents?: Record<number, unknown>;
}

export interface FrontPageResponse {
  page: FrontPage;
}

export interface FrontApiError {
  code: number;
  message: string;
}

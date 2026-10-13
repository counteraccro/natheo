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
}

export interface FrontPaginationTranslate {
  of: string;
  label: string;
  first: string;
  previous: string;
  next: string;
  last: string;
}

export interface FrontCommentTranslate {
  title: string;
  nbComments: string;
  timeAgo: string;
  validate: string;
  moderate: string;
  waiting: string;
  formModerateLabel: string;
  formModeratePlaceHolder: string;
  formModerateCancel: string;
  formModerateSubmit: string;
  successValidate: string;
  successModerate: string;
  successWaiting: string;
  btnNewComment: string;
  btnNewCommentCancel: string;
  btnNewCommentSubmit: string;
  formNewCommentPseudoLabel: string;
  formNewCommentPseudoPlaceholder: string;
  formNewCommentPseudoError: string;
  formNewCommentEmailLabel: string;
  formNewCommentEmailPlaceholder: string;
  formNewCommentCommentLabel: string;
  formNewCommentCommentPlaceholder: string;
  formNewCommentCommentError: string;
  formNewCommentSuccessMessage: string;
  moderationPrefix: string;
  pagination: FrontPaginationTranslate;
}

export interface FrontArticleFooterTranslate {
  edit: string;
  statPublication: string;
  infoDraft: string;
  comment: FrontCommentTranslate;
}

export interface FrontListingTranslate {
  noImage: string;
  by: string;
  empty: string;
  nbPages: string;
}

export interface FrontMainTranslate {
  articleFooter: FrontArticleFooterTranslate;
  breadcrumb: string;
  headerImgAlt: string;
  listing: FrontListingTranslate;
  pagination: FrontPaginationTranslate;
}

export interface FrontHeaderTranslate {
  login: string;
  logout: string;
  openMenu: string;
  darkMode: string;
}

export interface FrontFooterTranslate {
  credit: string;
  templateVersion: string;
  adminLink: string;
  githubLink: string;
  sitemapLink: string;
  frLink: string;
  esLink: string;
  enLink: string;
}

export interface FrontErrorApiTranslate {
  [code: string]: string;
  default: string;
  tokenMissing: string;
  back: string;
}

export interface FrontTranslate {
  errorApi: FrontErrorApiTranslate;
  footer: FrontFooterTranslate;
  header: FrontHeaderTranslate;
  main: FrontMainTranslate;
}

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
}

export interface FrontPageResponse {
  page: FrontPage;
}

export interface FrontApiError {
  code: number;
  message: string;
}

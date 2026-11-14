/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Traductions du site public (FrontTranslate), communes à tous les thèmes
 */

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
  sortLabel: string;
  sortDesc: string;
  sortAsc: string;
  statusWait: string;
  statusModerate: string;
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
  btnNewCommentSubmit: string;
  formNewCommentPseudoLabel: string;
  formNewCommentPseudoPlaceholder: string;
  formNewCommentEmailLabel: string;
  formNewCommentEmailPlaceholder: string;
  formNewCommentCommentLabel: string;
  formNewCommentCommentPlaceholder: string;
  formNewCommentSuccessMessage: string;
  moderationPrefix: string;
  pagination: FrontPaginationTranslate;
}

/** Formes d'un libellé accordé à un nombre, « # » remplacé par le nombre */
export interface FrontPluralTranslate {
  one: string;
  other: string;
}

export interface FrontArticleFooterTranslate {
  reads: FrontPluralTranslate;
  infoDraft: string;
  comment: FrontCommentTranslate;
}

export interface FrontListingTranslate {
  by: string;
  empty: string;
  pages: FrontPluralTranslate;
}

export interface FrontMainTranslate {
  articleFooter: FrontArticleFooterTranslate;
  breadcrumb: string;
  editLink: string;
  loading: string;
  headerImgAlt: string;
  updatedAt: string;
  readingTime: FrontPluralTranslate;
  tagsLabel: string;
  listing: FrontListingTranslate;
  pagination: FrontPaginationTranslate;
}

export interface FrontHeaderTranslate {
  login: string;
  logout: string;
  openMenu: string;
  darkMode: string;
  skipLink: string;
  closeMenu: string;
  localeLabel: string;
}

export interface FrontMenuTranslate {
  headerLabel: string;
  sideLabel: string;
  footerLabel: string;
  /** Libellé du bouton qui ouvre un sous-menu, « {label} » à la place du libellé de l'élément */
  submenu: string;
}

export interface FrontFooterTranslate {
  credit: string;
  templateVersion: string;
  adminLink: string;
  sitemapLink: string;
  frLink: string;
  esLink: string;
  enLink: string;
  socialLabel: string;
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
  menu: FrontMenuTranslate;
  main: FrontMainTranslate;
}

/**
 * @author Gourdon Aymeric
 * @version 1.2
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
  /** Libellés courts des boutons d'une barre d'icônes */
  menuButton: string;
  modeButton: string;
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
  consentLink: string;
}

/** Repli des libellés du bandeau de consentement laissés vides dans les options système */
export interface FrontConsentTranslate {
  regionLabel: string;
  accept: string;
  refuse: string;
}

export interface FrontErrorApiTranslate {
  [code: string]: string;
  default: string;
  tokenMissing: string;
  back: string;
}

/** Liste des pages d'une catégorie, à côté de la page ouverte */
export interface FrontThreadTranslate {
  order: string;
  more: string;
  end: string;
  button: string;
  /** Annonce de la page ouverte sans rechargement, « {title} » à la place de son titre */
  opened: string;
}

export interface FrontTranslate {
  /** Libellé de chaque catégorie de page, par slug */
  categories: Record<string, string>;
  consent: FrontConsentTranslate;
  errorApi: FrontErrorApiTranslate;
  footer: FrontFooterTranslate;
  header: FrontHeaderTranslate;
  menu: FrontMenuTranslate;
  main: FrontMainTranslate;
  thread: FrontThreadTranslate;
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
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
  editLink: string;
  loading: string;
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

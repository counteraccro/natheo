/**
 * @author Gourdon Aymeric
 * @version 1.3
 * Données de démarrage de l'application du back-office, lues dans la page (AdminAppBootstrapService)
 */

/** Access token de la session API du back-office et son expiration (ISO 8601) */
export interface AdminAccessToken {
  accessToken: string;
  expiresAt: string;
}

export interface AdminBootstrapApi extends AdminAccessToken {
  /** Préfixe de l'API v2 (/api/v2) */
  prefix: string;
  /** Adresse de renouvellement de l'access token */
  tokenUrl: string;
}

export interface AdminBootstrapUser {
  id: number;
  login: string;
  name: string;
  initials: string;
  email: string;
  avatar: string | null;
  /** Rôles atteignables par la hiérarchie des rôles (ROLE_ADMIN…) */
  roles: string[];
  /** Nom traduit du rôle le plus élevé */
  role: string;
  /** Nom de l'utilisateur d'origine pendant une usurpation */
  impersonating: string | null;
}

export interface AdminBootstrapUrls {
  dashboard: string;
  logout: string;
  impersonationExit: string | null;
  myAccount: string;
  myOptions: string;
  search: string;
  notifications: string;
}

export interface AdminBootstrapOptions {
  notifications: boolean;
  /** Confirmation avant de quitter un formulaire modifié */
  confirmLeave: boolean;
  itemsPerPage: number;
  /** Commentaires ouverts sur le site (OS_OPEN_COMMENT) */
  openComment: boolean;
  /** Nouveaux commentaires en attente de validation (OS_NEW_COMMENT_WAIT_VALIDATION) */
  commentWaitValidation: boolean;
}

export interface AdminDebugUser {
  id: number;
  login: string;
  /** Nom traduit du rôle le plus élevé */
  role: string;
  avatar: string | null;
  url: string;
}

export interface AdminBootstrapDebug {
  php: string;
  env: string;
  database: string;
  branch: string | null;
  commit: string | null;
  commitDate: string | null;
  version: string;
  users: AdminDebugUser[];
}

/** Thème du back-office proposé à l'utilisateur */
export interface AdminBootstrapTheme {
  /** Identifiant du thème (natheo_studio) */
  id: string;
  /** Nom traduit */
  label: string;
}

/** Thème actif du site public, pour montrer le texte comme sur le site */
export interface AdminBootstrapSiteTheme {
  /** Identifiant (natheo_horizon), posé en data-front-theme */
  id: string;
  label: string;
  /** Classe du texte des blocs (nh-prose) */
  textClass: string;
  /** Feuille des couleurs, polices et texte du thème, null si le thème n'en a pas */
  stylesheet: string | null;
}

export interface AdminBootstrap {
  api: AdminBootstrapApi;
  locale: string;
  locales: string[];
  /** /admin/<langue>/ */
  basePath: string;
  user: AdminBootstrapUser;
  site: { name: string; version: string; url: string; theme: AdminBootstrapSiteTheme | null };
  urls: AdminBootstrapUrls;
  options: AdminBootstrapOptions;
  /** Thème de l'utilisateur, natheo_studio pour une valeur inconnue */
  theme: string;
  themes: AdminBootstrapTheme[];
  /** Messages bruts (ICU) par domaine */
  messages: Record<string, Record<string, string>>;
  /** Présent en environnement dev seulement */
  debug: AdminBootstrapDebug | null;
}

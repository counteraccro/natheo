/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Props passées par IndexController au composant racine de chaque thème
 */
import type { FrontTranslate } from '@/ts/Front/FrontTranslate.type';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { PageContent } from '@/ts/Front/Api/PageContent.type';

export interface FrontThemeUrls {
  /** Préfixe de l'API v2 (/api/v2) */
  api: string;
  adminAuth: string;
  sitemap: string;
  logout: string;
  /** Accueil du site par locale */
  home: Record<string, string>;
}

/** Utilisateur connecté au back-office */
export interface FrontUser {
  login: string;
  avatar: string | null;
  canModerate: boolean;
  /** Écran d'édition de la page affichée, null sans droit de modification */
  editUrl: string | null;
}

/** Brouillon prévisualisé : la page et ses contenus complets, servis sans appel à l'API */
export interface FrontPreview {
  page: Page;
  contents: PageContent[];
}

/** Bandeau de consentement aux scripts des options système, libellé vide quand le thème affiche le sien */
export interface FrontConsent {
  /** Texte déjà converti en HTML par le serveur */
  text: string;
  accept: string;
  refuse: string;
}

export interface FrontThemeDatas {
  slug: string | null;
  locale: string;
  locales: string[];
  apiToken: string;
  /** Access token de la session API de l'utilisateur connecté, chaîne vide sinon */
  userToken: string;
  user: FrontUser | null;
  preview: FrontPreview | null;
  /** Null quand le bandeau n'est pas nécessaire (désactivé, aucun script, prévisualisation) */
  consent: FrontConsent | null;
}

export interface FrontThemeProps {
  urls: FrontThemeUrls;
  datas: FrontThemeDatas;
  translate: FrontTranslate;
}

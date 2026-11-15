/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de l'aperçu du menu au design du thème du site public (MenuPreview.vue)
 */
import type { Menu, PageLocales } from '@/ts/Menu/Menu.type';
import type { MenuPosition, MenuType } from '@/ts/Front/Api/Menu.type';
import type { PageCategory } from '@/ts/Front/Api/Page.type';

/** Slug de l'API v2 de chaque type et de chaque position, par valeur */
export interface MenuPreviewSlugs {
  types: Record<string, MenuType>;
  positions: Record<string, MenuPosition>;
}

/** Catégorie et état publié d'une page */
export interface MenuPreviewPage {
  category: PageCategory | null;
  published: boolean;
}

/** Données dont la conversion du menu au format de l'API v2 a besoin */
export interface MenuPreviewInput {
  menu: Menu;
  locale: string;
  slugs: MenuPreviewSlugs;
  /** Titres et URL des pages par langue */
  pages: Record<string, PageLocales>;
  previewPages: Record<string, MenuPreviewPage>;
}

export interface MenuPreviewTranslate {
  iframe_title: string;
  width_label: string;
  width_desktop: string;
  width_mobile: string;
  scheme_label: string;
  scheme_light: string;
  scheme_dark: string;
}

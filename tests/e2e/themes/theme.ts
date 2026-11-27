/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Gestes d'un écran de liste qui diffèrent d'un thème du back-office à l'autre
 */
import type { Locator, Page } from '@playwright/test';

/** Écran de liste : adresse et titre */
export interface ListScreen {
  path: string;
  title: string;
}

export const TAG_LIST: ListScreen = { path: '/admin/fr/tags', title: 'Gestion des tags' };
export const FAQ_LIST: ListScreen = { path: '/admin/fr/faqs', title: 'Gestion des FAQ' };
export const COMMENT_LIST: ListScreen = { path: '/admin/fr/comments', title: 'Commentaires' };

export interface ThemeScreens {
  /** Ouvre une liste (les tags par défaut) et attend ses lignes */
  openList(page: Page, list?: ListScreen): Promise<void>;
  /** Ligne d'un élément, d'après son libellé */
  row(page: Page, label: string, list?: ListScreen): Locator;
  /** Lignes affichées */
  rows(page: Page, list?: ListScreen): Locator;
  /** Lance une action de ligne (Désactiver, Activer, Supprimer) */
  rowAction(page: Page, label: string, action: string, list?: ListScreen): Promise<void>;
  /** Actions de ligne proposées à l'utilisateur (vide si aucune) */
  rowActions(page: Page, label: string, list?: ListScreen): Locator;
  /** Ouvre un élément depuis sa ligne */
  openRow(page: Page, label: string, list?: ListScreen): Promise<void>;
  /** Revient à la liste depuis le formulaire (liste déjà affichée à côté dans Natheo-Atelier) */
  backToList(page: Page): Promise<void>;
  /** Recherche dans la liste */
  search(page: Page, term: string): Promise<void>;
  /** Tri par libellé */
  sortByLabel(page: Page): Promise<void>;
  /** Ouvre le menu des options de la liste */
  openOptions(page: Page): Promise<void>;
  /** Action groupée sur la sélection */
  bulkAction(page: Page, action: string): Promise<void>;
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Gestes d'un écran de liste qui diffèrent d'un thème du back-office à l'autre
 */
import type { Locator, Page } from '@playwright/test';

export interface ThemeScreens {
  /** Ouvre la liste des tags et attend ses lignes */
  openList(page: Page): Promise<void>;
  /** Ligne d'un élément, d'après son libellé */
  row(page: Page, label: string): Locator;
  /** Lignes affichées */
  rows(page: Page): Locator;
  /** Lance une action de ligne (Désactiver, Activer, Supprimer) */
  rowAction(page: Page, label: string, action: string): Promise<void>;
  /** Actions de ligne proposées à l'utilisateur (vide si aucune) */
  rowActions(page: Page, label: string): Locator;
  /** Recherche dans la liste */
  search(page: Page, term: string): Promise<void>;
  /** Tri par libellé */
  sortByLabel(page: Page): Promise<void>;
  /** Ouvre le menu des options de la liste */
  openOptions(page: Page): Promise<void>;
  /** Action groupée sur la sélection */
  bulkAction(page: Page, action: string): Promise<void>;
}

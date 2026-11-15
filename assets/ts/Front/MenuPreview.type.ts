/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Messages échangés entre l'éditeur de menu et l'aperçu du menu au design du thème (iframe)
 */
import type { Menu } from '@/ts/Front/Api/Menu.type';

/** Marque des messages de l'aperçu, pour ignorer les autres messages reçus par la fenêtre */
export type MenuPreviewSource = 'natheo-menu-preview';

/** Aperçu → éditeur : prêt à recevoir le menu */
export interface MenuPreviewReadyMessage {
  source: MenuPreviewSource;
  type: 'ready';
}

/** Aperçu → éditeur : hauteur du contenu, en pixels */
export interface MenuPreviewHeightMessage {
  source: MenuPreviewSource;
  type: 'height';
  height: number;
}

/** Éditeur → aperçu : menu en cours d'édition, au format de l'API v2 */
export interface MenuPreviewMenuMessage {
  source: MenuPreviewSource;
  type: 'menu';
  menu: Menu;
}

/** Éditeur ↔ aperçu : mode sombre ou clair, choisi dans la barre de l'éditeur ou par le bouton du thème */
export interface MenuPreviewSchemeMessage {
  source: MenuPreviewSource;
  type: 'scheme';
  dark: boolean;
}

export type MenuPreviewMessage =
  MenuPreviewReadyMessage | MenuPreviewHeightMessage | MenuPreviewMenuMessage | MenuPreviewSchemeMessage;

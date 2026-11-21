/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Messages échangés entre un écran du back-office et l'aperçu au rendu du thème du site (iframe) : menu, tag…
 */

/** Marque des messages de l'aperçu, pour ignorer les autres messages reçus par la fenêtre */
export type PreviewSource = 'natheo-preview';

/** Aperçu → écran : prêt à recevoir les données */
export interface PreviewReadyMessage {
  source: PreviewSource;
  type: 'ready';
}

/** Aperçu → écran : hauteur du contenu, en pixels */
export interface PreviewHeightMessage {
  source: PreviewSource;
  type: 'height';
  height: number;
}

/** Écran → aperçu : élément en cours d'édition, au format de l'API v2 */
export interface PreviewDataMessage<T = unknown> {
  source: PreviewSource;
  type: 'data';
  data: T;
}

/** Écran ↔ aperçu : mode sombre ou clair, choisi dans l'écran ou par le bouton du thème */
export interface PreviewSchemeMessage {
  source: PreviewSource;
  type: 'scheme';
  dark: boolean;
}

export type PreviewMessage<T = unknown> =
  PreviewReadyMessage | PreviewHeightMessage | PreviewDataMessage<T> | PreviewSchemeMessage;

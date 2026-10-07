/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de la modale Mediatheque de l'éditeur markdown
 */

export interface MediathequeTranslate {
  title: string;
  no_media: string;
  no_search: string;
  search_placeholder: string;
  folder: string;
  img: string;
  file: string;
  files: string;
  root: string;
  error: string;
}

/** Élément brut renvoyé par la route admin_media_load_medias */
export interface MediathequeApiItem {
  type: 'folder' | 'media';
  id: number;
  name: string;
  title?: string | null;
  webPath?: string;
  thumbnail?: string;
}

export interface MediaItem {
  id: number;
  name: string;
  /** webPath — inséré dans le markdown */
  url: string;
  /** thumbnail — affiché dans la modale */
  thumb: string;
  isImage: boolean;
}

export interface FolderItem {
  id: number;
  name: string;
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de AdminHeader et de l'apparence du back-office
 */
export type ThemeColor = 'violet' | 'blue' | 'green' | 'red';
export type ColorMode = 'light' | 'dark';

/** Section courante : groupe du menu puis page, avec l'icône de la page (tracé reçu de l'API) */
export interface HeaderSection {
  group?: string;
  label: string;
  icon?: string;
}

export interface HeaderShortcut {
  id: string;
  label: string;
  href: string;
  icon?: string;
}

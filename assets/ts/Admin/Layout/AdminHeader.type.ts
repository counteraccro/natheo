/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Types de AdminHeader et de l'apparence du back-office
 */
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export type ThemeColor = 'violet' | 'blue' | 'green' | 'red';
export type ColorMode = 'light' | 'dark';

/** Section courante : groupe du menu puis page, avec l'icône de la page (tracé reçu de l'API ou icône du registre) */
export interface HeaderSection {
  group?: string;
  label: string;
  icon?: string;
  iconName?: IconName;
}

/** Disposition du back-office proposée par le menu Apparence (thème) */
export interface LayoutChoice {
  id: string;
  label: string;
}

export interface HeaderShortcut {
  id: string;
  label: string;
  href: string;
  icon?: string;
}

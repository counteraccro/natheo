/**
 * @author Gourdon Aymeric
 * @version 1.0
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

export interface HeaderShortcut {
  id: string;
  label: string;
  href: string;
  icon?: string;
}

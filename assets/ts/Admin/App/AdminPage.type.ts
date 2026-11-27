/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écran affiché, tel qu'il se présente à la barre du haut et à l'onglet du navigateur
 */
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export interface AdminPage {
  title: string;
  icon?: IconName;
}

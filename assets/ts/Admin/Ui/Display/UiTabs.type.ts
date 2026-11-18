/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de UiTabs
 */
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export interface TabItem {
  id: string;
  label: string;
  icon?: IconName;
  /** Onglet marqué comme contenant des erreurs */
  invalid?: boolean;
}

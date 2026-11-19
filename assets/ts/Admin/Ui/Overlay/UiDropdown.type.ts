/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de UiDropdown et de ses éléments
 */
export type DropdownItemType = 'item' | 'checkbox' | 'radio';

export interface DropdownContext {
  close: (restoreFocus?: boolean) => void;
}

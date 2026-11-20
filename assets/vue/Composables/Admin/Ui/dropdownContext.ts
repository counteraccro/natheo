/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien entre un UiDropdown et ses éléments
 */
import type { InjectionKey } from 'vue';
import type { DropdownContext } from '@/ts/Admin/Ui/Overlay/UiDropdown.type';

export const DROPDOWN: InjectionKey<DropdownContext> = Symbol('UiDropdown');

/** Classes communes aux éléments d'un menu */
export const MENU_ITEM_CLASSES =
  'flex min-h-9 w-full cursor-pointer items-center gap-2.5 rounded-sm border-0 bg-transparent px-2.5 text-left text-base text-fg no-underline hover:bg-surface focus:bg-surface focus:outline-none aria-disabled:cursor-not-allowed aria-disabled:opacity-50';

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource Menu de l'API v2
 */
import type { JsonLdResource } from '@/ts/Front/Api/Hydra.type';
import type { PageCategory } from '@/ts/Front/Api/Page.type';

export type MenuPosition = 'position_header' | 'position_right' | 'position_footer' | 'position_left';

export type MenuType =
  | 'header_side_bar'
  | 'header_menu_deroulant'
  | 'header_menu_deroulant_big_menu'
  | 'header_menu_deroulant_big_menu_2_colonnes'
  | 'header_menu_deroulant_big_menu_3_colonnes'
  | 'header_menu_deroulant_big_menu_4_colonnes'
  | 'left_right_side_bar'
  | 'left_right_side_bar_accordeon'
  | 'footer_colonnes'
  | 'footer_1_row_right'
  | 'footer_1_row_center';

export interface MenuElement {
  id: number;
  label: string;
  target: string;
  url: string | null;
  pageSlug: string | null;
  pageCategory: PageCategory | null;
  column: number;
  row: number;
  children: MenuElement[];
}

export interface Menu extends JsonLdResource {
  id: number;
  position: MenuPosition;
  type: MenuType;
  elements: MenuElement[];
}

/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Menus de la page par position, disposition de chaque type de menu et lien de chaque élément
 * (page facultative pour le seul calcul des liens)
 */
import { computed, type ComputedRef, type Ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { Menu, MenuElement, MenuPosition, MenuType } from '@/ts/Front/Api/Menu.type';

/**
 * Disposition d'un menu, traduite en HTML et en classes par le thème :
 * - sidebar : panneau latéral ouvert depuis l'en-tête
 * - dropdown : sous-menus déroulants
 * - mega : sous-menus en grand panneau de `columns` colonnes
 * - vertical / accordion : menu de côté, déplié ou en accordéon
 * - columns : pied de page, un élément racine par colonne
 * - row : pied de page sur une ligne, aligné selon `align`
 */
export interface MenuLayout {
  kind: 'sidebar' | 'dropdown' | 'mega' | 'vertical' | 'accordion' | 'columns' | 'row';
  columns: number;
  align: 'left' | 'center' | 'right';
}

const LAYOUTS: Record<MenuType, MenuLayout> = {
  header_side_bar: { kind: 'sidebar', columns: 1, align: 'left' },
  header_menu_deroulant: { kind: 'dropdown', columns: 1, align: 'left' },
  header_menu_deroulant_big_menu: { kind: 'mega', columns: 1, align: 'left' },
  header_menu_deroulant_big_menu_2_colonnes: { kind: 'mega', columns: 2, align: 'left' },
  header_menu_deroulant_big_menu_3_colonnes: { kind: 'mega', columns: 3, align: 'left' },
  header_menu_deroulant_big_menu_4_colonnes: { kind: 'mega', columns: 4, align: 'left' },
  left_right_side_bar: { kind: 'vertical', columns: 1, align: 'left' },
  left_right_side_bar_accordeon: { kind: 'accordion', columns: 1, align: 'left' },
  footer_colonnes: { kind: 'columns', columns: 4, align: 'left' },
  footer_1_row_right: { kind: 'row', columns: 1, align: 'right' },
  footer_1_row_center: { kind: 'row', columns: 1, align: 'center' },
};

export interface Menus {
  menus: ComputedRef<Partial<Record<MenuPosition, Menu>>>;
  layoutOf(menu: Menu): MenuLayout;
  /** URL d'un élément : lien externe ou page interne, null pour un simple titre */
  hrefOf(element: MenuElement): string | null;
  /** Élément menant à la page affichée, ou parent d'un tel élément */
  isActive(element: MenuElement): boolean;
}

export function useMenus(page?: Ref<Page | null>): Menus {
  const context = useFrontContext();

  const menus = computed(() => Object.fromEntries((page?.value?.menus ?? []).map((menu) => [menu.position, menu])));

  function layoutOf(menu: Menu): MenuLayout {
    return LAYOUTS[menu.type] ?? LAYOUTS.header_menu_deroulant;
  }

  function hrefOf(element: MenuElement): string | null {
    if (element.url) {
      return element.url;
    }
    if (element.pageSlug && element.pageCategory) {
      return context.pageUrl(element.pageCategory, element.pageSlug);
    }
    return null;
  }

  function isActive(element: MenuElement): boolean {
    const slug = context.datas.slug ?? page?.value?.slug ?? null;
    return (slug !== null && element.pageSlug === slug) || element.children.some(isActive);
  }

  return { menus, layoutOf, hrefOf, isActive };
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menus de la page par position, et lien de chaque élément (page facultative pour le seul calcul des liens)
 */
import { computed, type ComputedRef, type Ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { Menu, MenuElement, MenuPosition } from '@/ts/Front/Api/Menu.type';

export interface Menus {
  menus: ComputedRef<Partial<Record<MenuPosition, Menu>>>;
  /** URL d'un élément : lien externe ou page interne, null pour un simple titre */
  hrefOf(element: MenuElement): string | null;
}

export function useMenus(page?: Ref<Page | null>): Menus {
  const context = useFrontContext();

  const menus = computed(() => Object.fromEntries((page?.value?.menus ?? []).map((menu) => [menu.position, menu])));

  function hrefOf(element: MenuElement): string | null {
    if (element.url) {
      return element.url;
    }
    if (element.pageSlug && element.pageCategory) {
      return context.pageUrl(element.pageCategory, element.pageSlug);
    }
    return null;
  }

  return { menus, hrefOf };
}

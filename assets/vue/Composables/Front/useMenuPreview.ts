/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu au design du thème, affiché dans une iframe de l'éditeur de menu
 */
import { computed, type ComputedRef, type ShallowRef } from 'vue';
import { usePreviewFrame } from '@/vue/Composables/Front/usePreviewFrame';
import type { Menu } from '@/ts/Front/Api/Menu.type';
import type { Page } from '@/ts/Front/Api/Page.type';

export interface MenuPreview {
  /** Menu reçu de l'éditeur, null avant le premier envoi */
  menu: ShallowRef<Menu | null>;
  /** Page qui ne porte que le menu, à donner aux composants du thème qui lisent les menus d'une page */
  page: ComputedRef<Page | null>;
}

/**
 * Page de prévisualisation : seuls ses menus sont lus par les composants du thème
 */
function menuPreviewPage(menu: Menu): Page {
  return {
    '@id': '',
    '@type': 'Page',
    id: 0,
    slug: '',
    title: '',
    category: 'page',
    headerImage: null,
    author: null,
    tags: [],
    createdAt: null,
    updatedAt: null,
    render: 'one_block',
    landingPage: false,
    openComment: false,
    reads: 0,
    seo: [],
    translations: [],
    contents: [],
    menus: [menu],
  };
}

export function useMenuPreview(): MenuPreview {
  const menu = usePreviewFrame<Menu>();
  const page = computed(() => (menu.value === null ? null : menuPreviewPage(menu.value)));
  return { menu, page };
}

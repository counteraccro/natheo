/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu au design du thème, affiché dans une iframe de l'éditeur de menu : menu reçu de l'éditeur,
 * hauteur renvoyée pour ajuster le cadre, liens sans navigation
 */
import { computed, onBeforeUnmount, onMounted, shallowRef, type ComputedRef, type ShallowRef } from 'vue';
import { postMenuPreviewMessage, readMenuPreviewMessage } from '@/vue/Composables/Global/menuPreviewMessage';
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
  const menu = shallowRef<Menu | null>(null);
  const page = computed(() => (menu.value === null ? null : menuPreviewPage(menu.value)));
  const observer = new ResizeObserver(() => {
    postMenuPreviewMessage(window.parent, {
      type: 'height',
      height: Math.ceil(document.body.getBoundingClientRect().height),
    });
  });

  function onMessage(event: MessageEvent): void {
    const message = readMenuPreviewMessage(event, window.parent);
    if (message?.type === 'menu') {
      menu.value = message.menu;
    } else if (message?.type === 'scheme') {
      document.documentElement.classList.toggle('dark', message.dark);
    }
  }

  // Les liens mènent au site : l'aperçu garde les sous-menus actifs mais ne navigue pas
  function onClick(event: MouseEvent): void {
    if ((event.target as Element | null)?.closest('a[href]')) {
      event.preventDefault();
    }
  }

  onMounted(() => {
    window.addEventListener('message', onMessage);
    document.addEventListener('click', onClick, true);
    observer.observe(document.body);
    postMenuPreviewMessage(window.parent, { type: 'ready' });
  });

  onBeforeUnmount(() => {
    window.removeEventListener('message', onMessage);
    document.removeEventListener('click', onClick, true);
    observer.disconnect();
  });

  return { menu, page };
}

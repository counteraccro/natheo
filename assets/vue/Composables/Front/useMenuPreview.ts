/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu au design du thème, affiché dans une iframe de l'éditeur de menu : menu reçu de l'éditeur,
 * hauteur renvoyée pour ajuster le cadre (sous-menus ouverts compris), liens sans navigation
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

/**
 * Hauteur du contenu, sous-menus ouverts compris : ils sont positionnés par-dessus la page et ne comptent pas dans
 * la hauteur du body. Un panneau fixe (menu latéral) compte pour la hauteur de son contenu.
 */
function contentHeight(): number {
  let bottom = document.body.getBoundingClientRect().bottom;
  for (const element of Array.from(document.body.querySelectorAll<HTMLElement>('*'))) {
    const rect = element.getBoundingClientRect();
    if (rect.height === 0) {
      continue;
    }
    const position = getComputedStyle(element).position;
    if (position === 'fixed') {
      bottom = Math.max(bottom, rect.top + element.scrollHeight);
    } else if (position === 'absolute') {
      bottom = Math.max(bottom, rect.bottom);
    }
  }
  return Math.ceil(bottom + window.scrollY);
}

export function useMenuPreview(): MenuPreview {
  const menu = shallowRef<Menu | null>(null);
  const page = computed(() => (menu.value === null ? null : menuPreviewPage(menu.value)));
  let frame = 0;
  let sentHeight = 0;
  const resize = new ResizeObserver(scheduleHeight);
  const mutations = new MutationObserver(scheduleHeight);

  // Une mesure par image, quel que soit le nombre de changements
  function scheduleHeight(): void {
    if (frame === 0) {
      frame = requestAnimationFrame(sendHeight);
    }
  }

  function sendHeight(): void {
    frame = 0;
    const height = contentHeight();
    if (height !== sentHeight) {
      sentHeight = height;
      postMenuPreviewMessage(window.parent, { type: 'height', height });
    }
  }

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
    resize.observe(document.body);
    mutations.observe(document.body, {
      subtree: true,
      childList: true,
      attributes: true,
      attributeFilter: ['class', 'style', 'open'],
    });
    scheduleHeight();
    postMenuPreviewMessage(window.parent, { type: 'ready' });
  });

  onBeforeUnmount(() => {
    window.removeEventListener('message', onMessage);
    document.removeEventListener('click', onClick, true);
    resize.disconnect();
    mutations.disconnect();
    cancelAnimationFrame(frame);
  });

  return { menu, page };
}

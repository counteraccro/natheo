/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un menu au design du thème, affiché dans une iframe de l'éditeur de menu : menu reçu de l'éditeur,
 * hauteur renvoyée pour ajuster le cadre (sous-menus et tiroirs ouverts compris), liens sans navigation
 */
import { computed, onBeforeUnmount, onMounted, shallowRef, type ComputedRef, type ShallowRef } from 'vue';
import { setPreviewColorScheme } from '@/vue/Composables/Front/useColorScheme';
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
 * Éléments positionnés en absolu ou en fixe : sous-menus et tiroirs, posés par-dessus la page, hors de la hauteur du body
 */
function positionedElements(): HTMLElement[] {
  return Array.from(document.body.querySelectorAll<HTMLElement>('*')).filter((element) => {
    // La barre de débogage de Symfony (développement) est fixée en bas du cadre et le bloquerait à sa hauteur
    if (element.closest('.sf-toolbar') !== null) {
      return false;
    }
    const position = getComputedStyle(element).position;
    return position === 'fixed' || position === 'absolute';
  });
}

/**
 * Hauteur du contenu, éléments positionnés visibles compris. Un panneau fixe compte pour la hauteur de son contenu.
 */
function contentHeight(positioned: HTMLElement[]): number {
  let bottom = document.body.getBoundingClientRect().bottom;
  for (const element of positioned) {
    const rect = element.getBoundingClientRect();
    if (rect.height === 0) {
      continue;
    }
    const fixed = getComputedStyle(element).position === 'fixed';
    bottom = Math.max(bottom, fixed ? rect.top + element.scrollHeight : rect.bottom);
  }
  return Math.ceil(bottom + window.scrollY);
}

export function useMenuPreview(): MenuPreview {
  const menu = shallowRef<Menu | null>(null);
  const page = computed(() => (menu.value === null ? null : menuPreviewPage(menu.value)));
  let frame = 0;
  let sentHeight = 0;
  let positioned: HTMLElement[] | null = null;
  const resize = new ResizeObserver(() => {
    positioned = null;
    scheduleHeight();
  });
  // Un changement de style seul (v-show, animation) ne déplace pas d'élément hors du flux : la liste est gardée
  const mutations = new MutationObserver((records) => {
    if (records.some((record) => record.attributeName !== 'style')) {
      positioned = null;
    }
    scheduleHeight();
  });

  // Une mesure par image, quel que soit le nombre de changements
  function scheduleHeight(): void {
    if (frame === 0) {
      frame = requestAnimationFrame(sendHeight);
    }
  }

  function sendHeight(): void {
    frame = 0;
    positioned ??= positionedElements();
    const height = contentHeight(positioned);
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
      setPreviewColorScheme(message.dark ? 'dark' : 'light');
    }
  }

  // Les liens mènent au site : l'aperçu garde les sous-menus actifs mais ne navigue pas
  function onClick(event: MouseEvent): void {
    if ((event.target as Element | null)?.closest('a[href]')) {
      event.preventDefault();
    }
  }

  // Clair par défaut, comme la barre d'outils de l'éditeur, en attendant son choix
  setPreviewColorScheme('light');

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

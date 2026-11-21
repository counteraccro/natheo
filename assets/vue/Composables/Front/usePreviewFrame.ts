/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Cadre d'un aperçu au rendu du thème du site, affiché dans une iframe d'un écran du back-office
 */
import { onBeforeUnmount, onMounted, shallowRef, type ShallowRef } from 'vue';
import { onPreviewColorSchemeToggle, setPreviewColorScheme } from '@/vue/Composables/Front/useColorScheme';
import { postPreviewMessage, readPreviewMessage } from '@/vue/Composables/Global/previewMessage';

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

/**
 * Cadre d'aperçu : données reçues de l'écran, hauteur renvoyée pour ajuster le cadre (sous-menus et tiroirs
 * ouverts compris), mode clair ou sombre, liens sans navigation
 */
export function usePreviewFrame<T>(): ShallowRef<T | null> {
  const data = shallowRef<T | null>(null);
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
      postPreviewMessage(window.parent, { type: 'height', height });
    }
  }

  function onMessage(event: MessageEvent): void {
    const message = readPreviewMessage<T>(event, window.parent);
    if (message?.type === 'data') {
      data.value = message.data;
    } else if (message?.type === 'scheme') {
      setPreviewColorScheme(message.dark ? 'dark' : 'light');
    }
  }

  // Les liens mènent au site : l'aperçu garde les menus actifs mais ne navigue pas
  function onClick(event: MouseEvent): void {
    if ((event.target as Element | null)?.closest('a[href]')) {
      event.preventDefault();
    }
  }

  // Clair par défaut, comme la barre d'outils de l'écran, en attendant son choix
  setPreviewColorScheme('light');
  onPreviewColorSchemeToggle((value) => postPreviewMessage(window.parent, { type: 'scheme', dark: value === 'dark' }));

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
    postPreviewMessage(window.parent, { type: 'ready' });
  });

  onBeforeUnmount(() => {
    window.removeEventListener('message', onMessage);
    document.removeEventListener('click', onClick, true);
    onPreviewColorSchemeToggle(null);
    resize.disconnect();
    mutations.disconnect();
    cancelAnimationFrame(frame);
  });

  return data;
}

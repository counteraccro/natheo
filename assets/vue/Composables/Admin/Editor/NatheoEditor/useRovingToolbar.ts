/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Barre d'outils au clavier (motif « toolbar ») : un seul arrêt de tabulation, flèches gauche et droite, Début, Fin
 */
import { onMounted, onUpdated, useTemplateRef } from 'vue';

/** Boutons de la barre, menus compris */
const ITEMS = 'button, [role="button"]';

/**
 * Gère le focus des boutons de la barre
 * @param refName nom de la référence de modèle posée sur la barre
 */
export function useRovingToolbar(refName = 'toolbar') {
  const toolbar = useTemplateRef<HTMLElement>(refName);
  let current: HTMLElement | null = null;

  const items = (): HTMLElement[] => (toolbar.value ? [...toolbar.value.querySelectorAll<HTMLElement>(ITEMS)] : []);

  /**
   * Garde un seul bouton atteignable par Tab : le dernier qui a eu le focus, le premier sinon
   * @param focused bouton qui reçoit le focus
   */
  const sync = (focused?: HTMLElement): void => {
    const list = items();
    current = focused ?? (current !== null && list.includes(current) ? current : (list[0] ?? null));
    for (const item of list) item.tabIndex = item === current ? 0 : -1;
  };

  const onFocusin = (event: FocusEvent): void => {
    const target = event.target as HTMLElement;
    if (items().includes(target)) sync(target);
  };

  const onKeydown = (event: KeyboardEvent): void => {
    const list = items();
    const index = list.indexOf(document.activeElement as HTMLElement);
    if (index === -1) return;
    const targets: Record<string, number> = {
      ArrowRight: (index + 1) % list.length,
      ArrowLeft: (index - 1 + list.length) % list.length,
      Home: 0,
      End: list.length - 1,
    };
    if (event.key in targets) {
      event.preventDefault();
      list[targets[event.key]].focus();
    }
  };

  onMounted(() => sync());
  onUpdated(() => sync());

  return { onFocusin, onKeydown };
}

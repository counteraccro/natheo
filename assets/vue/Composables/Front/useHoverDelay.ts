/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Action différée à la sortie du survol (fermeture d'un panneau), annulée au retour de la souris
 */
import { onBeforeUnmount } from 'vue';

/** Délai qui laisse le temps de passer d'un élément à son panneau */
export const HOVER_DELAY = 150;

export interface HoverDelay {
  /** Lance l'action après le délai, en remplaçant celle déjà en attente */
  schedule(): void;
  cancel(): void;
}

/** Vrai pour un pointeur de souris : le survol ne concerne ni le doigt ni le stylet */
export function isMouse(event: PointerEvent): boolean {
  return event.pointerType === 'mouse';
}

export function useHoverDelay(action: () => void, delay: number = HOVER_DELAY): HoverDelay {
  let timer: ReturnType<typeof setTimeout> | undefined;

  function cancel(): void {
    clearTimeout(timer);
    timer = undefined;
  }

  function schedule(): void {
    cancel();
    timer = setTimeout(() => {
      timer = undefined;
      action();
    }, delay);
  }

  onBeforeUnmount(cancel);
  return { schedule, cancel };
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Placement fixe d'un panneau flottant sous ou au-dessus de son bouton, retourné s'il manque de place et recalé dans
 * l'écran ; le panneau se ferme au défilement de la page et au redimensionnement
 */
import { computed, nextTick, onBeforeUnmount, ref, watch, type Ref } from 'vue';
import type {
  FloatingPlacement,
  FloatingPosition,
  FloatingRect,
  FloatingSide,
} from '@/ts/Admin/Ui/Overlay/UiFloating.type';

const MARGIN = 8;
const ARROW_INSET = 14;

/**
 * Position du panneau dans la fenêtre
 * @param reference rectangle du bouton
 * @param size largeur et hauteur du panneau
 * @param viewport largeur et hauteur de la fenêtre
 * @param placement côté et alignement souhaités
 * @param offset écart entre le bouton et le panneau
 */
export function computePosition(
  reference: FloatingRect,
  size: { width: number; height: number },
  viewport: { width: number; height: number },
  placement: FloatingPlacement,
  offset = 8
): FloatingPosition {
  const [wanted, align] = placement.split('-') as [FloatingSide, string];
  const below = reference.top + reference.height + offset;
  const above = reference.top - offset - size.height;
  const fitsBelow = below + size.height <= viewport.height - MARGIN;
  const fitsAbove = above >= MARGIN;

  let side: FloatingSide = wanted;
  if (wanted === 'bottom' && !fitsBelow && fitsAbove) side = 'top';
  if (wanted === 'top' && !fitsAbove && fitsBelow) side = 'bottom';

  let left = reference.left;
  if (align === 'end') left = reference.left + reference.width - size.width;
  if (align === 'center') left = reference.left + reference.width / 2 - size.width / 2;
  left = Math.max(MARGIN, Math.min(left, viewport.width - size.width - MARGIN));

  const center = reference.left + reference.width / 2 - left;
  const arrowX = Math.max(ARROW_INSET, Math.min(center, size.width - ARROW_INSET));

  return { top: side === 'bottom' ? below : above, left, side, arrowX };
}

/**
 * Suit la position du panneau tant qu'il est ouvert
 * @param reference bouton
 * @param floating panneau, rendu dans body
 * @param open état d'ouverture
 * @param placement côté et alignement souhaités
 * @param onDismiss appelé au défilement hors du panneau et au redimensionnement
 */
export function useFloating(
  reference: Ref<HTMLElement | null>,
  floating: Ref<HTMLElement | null>,
  open: Ref<boolean>,
  placement: FloatingPlacement,
  onDismiss: () => void
) {
  const position = ref<FloatingPosition>({
    top: 0,
    left: 0,
    side: placement.startsWith('top') ? 'top' : 'bottom',
    arrowX: 0,
  });

  const update = (): void => {
    if (!reference.value || !floating.value) return;
    const rect = reference.value.getBoundingClientRect();
    position.value = computePosition(
      { top: rect.top, left: rect.left, width: rect.width, height: rect.height },
      { width: floating.value.offsetWidth, height: floating.value.offsetHeight },
      { width: window.innerWidth, height: window.innerHeight },
      placement
    );
  };

  const onScroll = (event: Event): void => {
    if (floating.value && event.target instanceof Node && floating.value.contains(event.target)) return;
    onDismiss();
  };

  const stop = (): void => {
    window.removeEventListener('scroll', onScroll, true);
    window.removeEventListener('resize', onDismiss);
  };

  watch(
    open,
    async (value) => {
      stop();
      if (!value) return;
      await nextTick();
      update();
      window.addEventListener('scroll', onScroll, true);
      window.addEventListener('resize', onDismiss);
    },
    { immediate: true }
  );

  onBeforeUnmount(stop);

  const style = computed(() => ({
    top: `${position.value.top}px`,
    left: `${position.value.left}px`,
    '--ui-arrow-x': `${position.value.arrowX}px`,
  }));

  return { position, style, update };
}

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Progression de la lecture, de 0 à 1, d'un élément ou de toute la page
 */
import { onBeforeUnmount, onMounted, readonly, shallowRef, type Ref } from 'vue';

/**
 * @param target élément suivi, toute la page si absent ou null
 */
export function useReadingProgress(target?: Ref<HTMLElement | null>): Readonly<Ref<number>> {
  const progress = shallowRef(0);
  let frame = 0;
  // La hauteur change aussi quand les blocs finissent de charger, sans défilement
  const observer = new ResizeObserver(() => schedule());

  const measure = (): void => {
    frame = 0;
    const element = target?.value ?? document.documentElement;
    const top = element.getBoundingClientRect().top + window.scrollY;
    const distance = element.scrollHeight - window.innerHeight;
    progress.value = distance <= 0 ? 1 : Math.min(1, Math.max(0, (window.scrollY - top) / distance));
  };

  const schedule = (): void => {
    if (frame === 0) {
      frame = requestAnimationFrame(measure);
    }
  };

  onMounted(() => {
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
    observer.observe(target?.value ?? document.body);
    measure();
  });

  onBeforeUnmount(() => {
    window.removeEventListener('scroll', schedule);
    window.removeEventListener('resize', schedule);
    observer.disconnect();
    cancelAnimationFrame(frame);
  });

  return readonly(progress);
}

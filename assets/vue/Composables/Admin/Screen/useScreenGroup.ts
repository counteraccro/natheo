/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste du groupe d'écrans de la route courante, qu'un thème peut garder affichée à côté du formulaire
 */
import { computed, type ComputedRef } from 'vue';
import { useRoute, useRouter, type RouteLocationNormalizedLoaded, type Router } from 'vue-router';
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';

export interface ScreenGroup {
  /** Nom de la route de la liste */
  name: string;
  descriptor: CollectionScreenDescriptor;
  /** La route courante est la liste elle-même */
  isCollection: boolean;
}

/**
 * Groupe de la route, null hors d'un écran décrit par des données
 * @param router
 * @param route
 */
export function screenGroupOf(
  router: Pick<Router, 'getRoutes'>,
  route: RouteLocationNormalizedLoaded
): ScreenGroup | null {
  const name = route.meta.screenGroup;
  if (name === undefined) return null;
  const props = router.getRoutes().find((record) => record.name === name)?.props.default;
  if (typeof props !== 'object' || props === null || !('descriptor' in props)) return null;
  return {
    name,
    descriptor: props.descriptor as CollectionScreenDescriptor,
    isCollection: route.name === name,
  };
}

/**
 * Groupe de la route courante
 */
export function useScreenGroup(): ComputedRef<ScreenGroup | null> {
  const router = useRouter();
  const route = useRoute();
  return computed(() => screenGroupOf(router, route));
}

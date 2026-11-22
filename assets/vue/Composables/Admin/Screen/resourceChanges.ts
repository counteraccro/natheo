/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Signal d'écriture d'une ressource de l'API, pour qu'une liste affichée en même temps qu'un formulaire se recharge
 */

type Listener = () => void;

const listeners = new Map<string, Set<Listener>>();

/**
 * Annonce qu'un élément de la ressource a été créé, modifié ou supprimé
 * @param resource chemin de la collection (/admin/tags)
 */
export function notifyResourceChange(resource: string): void {
  listeners.get(resource)?.forEach((listener) => listener());
}

/**
 * Écoute les écritures d'une ressource
 * @param resource chemin de la collection
 * @param listener
 * @return arrêt de l'écoute
 */
export function onResourceChange(resource: string, listener: Listener): () => void {
  const set = listeners.get(resource) ?? new Set<Listener>();
  set.add(listener);
  listeners.set(resource, set);
  return () => {
    set.delete(listener);
    if (set.size === 0) listeners.delete(resource);
  };
}

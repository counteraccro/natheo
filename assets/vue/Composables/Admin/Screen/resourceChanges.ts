/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Signal d'écriture d'une ressource de l'API, pour qu'une liste et un formulaire affichés ensemble restent à jour
 */

/** Écriture annoncée : éléments touchés (tous si absent), suppression, auteur de l'écriture */
export interface ResourceChange {
  ids?: (string | number)[];
  deleted?: boolean;
  source?: object;
}

type Listener = (change: ResourceChange) => void;

const listeners = new Map<string, Set<Listener>>();

/**
 * Annonce qu'un élément de la ressource a été créé, modifié ou supprimé
 * @param resource chemin de la collection (/admin/tags)
 * @param change
 */
export function notifyResourceChange(resource: string, change: ResourceChange = {}): void {
  listeners.get(resource)?.forEach((listener) => listener(change));
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

/**
 * L'écriture touche l'élément id
 * @param change
 * @param id
 */
export function changeConcerns(change: ResourceChange, id: string | number | null): boolean {
  return id !== null && (change.ids === undefined || change.ids.some((candidate) => String(candidate) === String(id)));
}

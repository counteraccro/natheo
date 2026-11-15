/**
 * Registre des formulaires suivis avant de quitter une page du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */
import { reactive } from 'vue';

const sources = new Map<string, () => unknown>();
// Réactif pour que isDirty(key) puisse alimenter une propriété calculée
const snapshots = reactive<Record<string, string>>({});

/**
 * Sérialise les données d'un formulaire pour les comparer, un champ vidé valant un champ jamais rempli
 * @param source
 */
const serialize = (source: () => unknown): string =>
  JSON.stringify(source(), (_key, value) => (value === null ? '' : value));

/**
 * Suit les données d'un formulaire, leur état actuel servant de référence
 * @param key identifiant unique du formulaire dans la page
 * @param source fonction retournant les données du formulaire
 * @param dirty true si les données affichées ne sont pas sauvegardées (formulaire réaffiché après une erreur)
 */
const track = (key: string, source: () => unknown, dirty: boolean = false): void => {
  sources.set(key, source);
  // Une sérialisation JSON n'est jamais vide : le formulaire reste modifié jusqu'à sa sauvegarde
  snapshots[key] = dirty ? '' : serialize(source);
};

/**
 * Prend l'état actuel des données comme nouvelle référence, après une sauvegarde réussie
 * @param key
 */
const commit = (key: string): void => {
  const source = sources.get(key);
  if (source !== undefined) {
    snapshots[key] = serialize(source);
  }
};

/**
 * Arrête le suivi d'un formulaire
 * @param key
 */
const untrack = (key: string): void => {
  sources.delete(key);
  delete snapshots[key];
};

/**
 * Retourne true si le formulaire key diffère de sa référence, ou si l'un des formulaires suivis en diffère sans key
 * @param key
 */
const isDirty = (key?: string): boolean => {
  const keys = key === undefined ? Object.keys(snapshots) : [key];
  return keys.some((k) => {
    const source = sources.get(k);
    return k in snapshots && source !== undefined && serialize(source) !== snapshots[k];
  });
};

export const leaveGuard = { track, commit, untrack, isDirty };

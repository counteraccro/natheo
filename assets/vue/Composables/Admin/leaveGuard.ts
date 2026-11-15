/**
 * Registre des formulaires suivis avant de quitter une page du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */

type TrackedForm = {
  source: () => unknown;
  snapshot: string;
};

const forms = new Map<string, TrackedForm>();

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
 */
const track = (key: string, source: () => unknown): void => {
  forms.set(key, { source, snapshot: serialize(source) });
};

/**
 * Prend l'état actuel des données comme nouvelle référence, après une sauvegarde réussie
 * @param key
 */
const commit = (key: string): void => {
  const form = forms.get(key);
  if (form !== undefined) {
    form.snapshot = serialize(form.source);
  }
};

/**
 * Arrête le suivi d'un formulaire
 * @param key
 */
const untrack = (key: string): void => {
  forms.delete(key);
};

/**
 * Retourne true si un formulaire suivi diffère de sa référence
 */
const isDirty = (): boolean => {
  for (const form of forms.values()) {
    if (serialize(form.source) !== form.snapshot) {
      return true;
    }
  }
  return false;
};

export const leaveGuard = { track, commit, untrack, isDirty };

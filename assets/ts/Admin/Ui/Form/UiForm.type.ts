/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types des erreurs de validation d'un formulaire du back-office, lues dans la réponse 422 de l'API
 */
import type { ComputedRef } from 'vue';

/** Erreur d'une réponse 422 d'API Platform (liste violations du problem+json) */
export interface ApiViolation {
  propertyPath: string;
  message: string;
  code?: string | null;
}

/** Erreur affichée dans le récapitulatif : champ visé, son libellé et le message */
export interface FormErrorItem {
  path: string;
  fieldId: string | null;
  label: string;
  message: string;
}

/** État des erreurs d'un formulaire, rendu par useFormErrors() */
export interface FormErrors {
  /** Messages par chemin de propriété, '' pour une erreur du formulaire entier */
  all: ComputedRef<Record<string, string[]>>;
  count: ComputedRef<number>;
  /** Incrémenté à chaque réponse reçue, pour donner le focus au récapitulatif */
  version: ComputedRef<number>;
  set: (violations: ApiViolation[]) => void;
  messagesFor: (path: string) => string[];
  hasErrorsIn: (prefix: string) => boolean;
  clear: (path: string) => void;
  clearAll: () => void;
}

/** Contexte qu'un UiForm transmet à ses champs, onglets et récapitulatif */
export interface FormContext {
  errors: FormErrors;
  register: (path: string, label: string) => void;
  unregister: (path: string) => void;
  labelOf: (path: string) => string | undefined;
  fieldId: (path: string) => string;
  /** Rend un champ visible (onglet), puis lui donne le focus */
  reveal: (path: string) => Promise<void>;
  addRevealer: (revealer: (path: string) => boolean) => () => void;
}

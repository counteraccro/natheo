/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contexte qu'un UiField transmet au champ qu'il enveloppe
 */
import type { ComputedRef, Ref } from 'vue';

export interface FieldContext {
  id: ComputedRef<string>;
  /** Identifiant du libellé, pour un contrôle qu'un label for ne nomme pas (zone éditable) */
  labelId: ComputedRef<string>;
  describedBy: ComputedRef<string | undefined>;
  invalid: ComputedRef<boolean>;
  required: ComputedRef<boolean>;
  /** Compteur de caractères du champ, affiché sur la ligne des erreurs */
  counter: Ref<string | undefined>;
}

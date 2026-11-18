/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contexte qu'un UiField transmet au champ qu'il enveloppe
 */
import type { ComputedRef } from 'vue';

export interface FieldContext {
  id: ComputedRef<string>;
  describedBy: ComputedRef<string | undefined>;
  invalid: ComputedRef<boolean>;
  required: ComputedRef<boolean>;
}

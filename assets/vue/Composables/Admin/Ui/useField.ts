/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien entre un UiField et son champ : identifiant, aide et erreur reliées, état invalide et requis
 */
import { computed, inject, provide, useId, type ComputedRef, type InjectionKey } from 'vue';
import type { FieldContext } from '@/ts/Admin/Ui/Form/UiField.type';

const FIELD: InjectionKey<FieldContext> = Symbol('UiField');

/**
 * Fournit le contexte du champ enveloppé
 */
export function provideField(context: FieldContext): void {
  provide(FIELD, context);
}

/**
 * Attributs d'accessibilité du contrôle : ceux du UiField parent, sinon un identifiant propre
 * @param invalid état invalide propre au contrôle (valeur saisie incorrecte)
 */
export function useFieldControl(invalid?: ComputedRef<boolean>) {
  const field = inject(FIELD, null);
  const ownId = `ui-control-${useId()}`;
  return computed(() => ({
    id: field?.id.value ?? ownId,
    'aria-describedby': field?.describedBy.value,
    'aria-invalid': field?.invalid.value || invalid?.value ? true : undefined,
    required: field?.required.value || undefined,
  }));
}

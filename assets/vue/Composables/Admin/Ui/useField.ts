/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien entre un UiField et son champ : identifiant, aide et erreur reliées, état invalide et requis
 */
import {
  computed,
  inject,
  onBeforeUnmount,
  provide,
  useId,
  watchEffect,
  type ComputedRef,
  type InjectionKey,
} from 'vue';
import type { FieldContext } from '@/ts/Admin/Ui/Form/UiField.type';

const FIELD: InjectionKey<FieldContext> = Symbol('UiField');

/**
 * Fournit le contexte du champ enveloppé
 */
export function provideField(context: FieldContext): void {
  provide(FIELD, context);
}

/**
 * Coupe le contexte du UiField parent : un contrôle placé dessous garde son propre identifiant
 */
export function isolateField(): void {
  provide(FIELD, null as unknown as FieldContext);
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

/**
 * Identifiant du libellé du UiField parent, undefined hors d'un champ
 */
export function useFieldLabel(): ComputedRef<string | undefined> {
  const field = inject(FIELD, null);
  return computed(() => field?.labelId.value);
}

/**
 * Confie le compteur de caractères au UiField parent, qui l'aligne avec les erreurs
 * @param text texte du compteur, undefined quand il est masqué
 * @return true quand le UiField l'affiche, false quand le contrôle doit l'afficher lui-même
 */
export function useFieldCounter(text: ComputedRef<string | undefined>): boolean {
  const field = inject(FIELD, null);
  if (!field) return false;
  watchEffect(() => (field.counter.value = text.value));
  onBeforeUnmount(() => (field.counter.value = undefined));
  return true;
}

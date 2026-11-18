/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien entre un UiForm et ses champs, onglets et récapitulatif d'erreurs
 */
import { inject, type InjectionKey } from 'vue';
import type { FormContext } from '@/ts/Admin/Ui/Form/UiForm.type';

export const FORM: InjectionKey<FormContext> = Symbol('UiForm');

/**
 * Contexte du UiForm parent, null hors d'un formulaire
 */
export function useFormContext(): FormContext | null {
  return inject(FORM, null);
}

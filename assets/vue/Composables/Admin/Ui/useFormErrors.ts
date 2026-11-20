/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Erreurs de validation d'un formulaire : le serveur est la seule source des règles, ses violations (réponse 422)
 * sont rangées par champ dans l'ordre reçu
 */
import { computed, ref } from 'vue';
import type { ApiViolation, FormErrors } from '@/ts/Admin/Ui/Form/UiForm.type';

/**
 * Violations d'une réponse problem+json, liste vide si le corps n'en contient pas
 * @param body corps de la réponse 422
 */
export function violationsFrom(body: unknown): ApiViolation[] {
  const violations = (body as { violations?: unknown } | null)?.violations;
  if (!Array.isArray(violations)) return [];
  return violations.filter(
    (v): v is ApiViolation => typeof v?.propertyPath === 'string' && typeof v?.message === 'string'
  );
}

/**
 * Identifiant HTML stable d'un champ à partir de son chemin de propriété
 * @param path chemin (label, translations[en].label)
 */
export function fieldIdOf(path: string): string {
  return `ui-field-${path.replace(/[^\w-]+/g, '-').replace(/^-|-$/g, '')}`;
}

export function useFormErrors(): FormErrors {
  const errors = ref<Record<string, string[]>>({});
  const version = ref(0);

  const set = (violations: ApiViolation[]): void => {
    const grouped: Record<string, string[]> = {};
    for (const { propertyPath, message } of violations) {
      (grouped[propertyPath] ??= []).push(message);
    }
    errors.value = grouped;
    version.value++;
  };

  const clear = (path: string): void => {
    if (!(path in errors.value)) return;
    const { [path]: _removed, ...rest } = errors.value;
    errors.value = rest;
  };

  return {
    all: computed(() => errors.value),
    count: computed(() => Object.values(errors.value).reduce((total, list) => total + list.length, 0)),
    version: computed(() => version.value),
    set,
    messagesFor: (path) => errors.value[path] ?? [],
    hasErrorsIn: (prefix) =>
      Object.keys(errors.value).some(
        (path) => path === prefix || path.startsWith(`${prefix}.`) || path.startsWith(`${prefix}[`)
      ),
    clear,
    clearAll: () => {
      errors.value = {};
    },
  };
}

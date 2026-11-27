import { describe, expect, it } from 'vitest';
import { fieldIdOf, useFormErrors, violationsFrom } from '@/vue/Composables/Admin/Ui/useFormErrors';

const problem = {
  status: 422,
  violations: [
    { propertyPath: 'color', message: 'Couleur invalide', code: 'x' },
    { propertyPath: 'translations[en].label', message: 'Obligatoire' },
    { propertyPath: 'translations[en].label', message: 'Trop court' },
    { propertyPath: '', message: 'Ce tag existe déjà' },
    { propertyPath: 3, message: 'ignorée' },
  ],
};

describe('useFormErrors', () => {
  it('lit les violations valides d’une réponse 422', () => {
    expect(violationsFrom(problem)).toHaveLength(4);
    expect(violationsFrom({ title: 'Erreur' })).toEqual([]);
    expect(violationsFrom(null)).toEqual([]);
  });

  it('range les messages par champ, dans l’ordre reçu', () => {
    const errors = useFormErrors();
    errors.set(violationsFrom(problem));
    expect(Object.keys(errors.all.value)).toEqual(['color', 'translations[en].label', '']);
    expect(errors.messagesFor('translations[en].label')).toEqual(['Obligatoire', 'Trop court']);
    expect(errors.count.value).toBe(4);
    expect(errors.version.value).toBe(1);
  });

  it('repère les erreurs d’un onglet et les efface champ par champ', () => {
    const errors = useFormErrors();
    errors.set(violationsFrom(problem));
    expect(errors.hasErrorsIn('translations[en]')).toBe(true);
    expect(errors.hasErrorsIn('translations[fr]')).toBe(false);
    errors.clear('translations[en].label');
    expect(errors.hasErrorsIn('translations[en]')).toBe(false);
    errors.clearAll();
    expect(errors.count.value).toBe(0);
  });

  it('donne un identifiant HTML stable par champ', () => {
    expect(fieldIdOf('translations[en].label')).toBe('ui-field-translations-en-label');
  });
});

import { describe, expect, it } from 'vitest';
import faqModule from '@admin/Modules/Faq/module';
import { faqCollection, faqForm } from '@admin/Modules/Faq/screens';

const catalogues = import.meta.glob<string>('/translations/faq+intl-icu.*.yaml', {
  eager: true,
  query: '?raw',
  import: 'default',
});

/** Clés du catalogue, une par ligne « clé: valeur » */
const catalogue = (locale: string): Set<string> =>
  new Set(catalogues[`/translations/faq+intl-icu.${locale}.yaml`].split('\n').map((line) => line.split(':')[0]));

/** Clés de traduction des descriptions */
function keysOf(): string[] {
  const keys = [
    faqCollection.title,
    faqCollection.description!,
    faqCollection.create!.label,
    ...faqCollection.columns.map((column) => column.label),
    ...Object.values(faqCollection.messages),
    faqForm.title.create,
    faqForm.title.edit,
    faqForm.description!.create,
    faqForm.description!.edit,
    ...Object.values(faqForm.messages),
  ];
  faqForm.sections.forEach((section) => {
    keys.push(section.title);
    if (section.description) keys.push(section.description);
    section.fields.forEach((field) => {
      if (field.label) keys.push(field.label);
      if (field.help) keys.push(field.help);
    });
  });
  return keys;
}

describe('module FAQ', () => {
  it('déclare la liste, la création et l’édition', () => {
    expect(faqModule.id).toBe('faq');
    expect(faqModule.domains).toEqual(['faq']);
    expect(faqModule.routes.map((route) => [route.name, route.path])).toEqual([
      ['faq_index', '/faqs'],
      ['faq_new', '/faqs/new'],
      ['faq_edit', '/faqs/:id(\\d+)'],
    ]);
  });

  it.each(['fr', 'en', 'es'])('traduit chaque clé des écrans en %s', (locale) => {
    const messages = catalogue(locale);
    expect(keysOf().filter((key) => !messages.has(key))).toEqual([]);
  });

  it('ne laisse supprimer en groupe que les FAQ sans page', () => {
    const faq = { id: 1, title: 'Produit', pageCount: 0 } as Parameters<NonNullable<typeof faqCollection.deletable>>[0];
    expect(faqCollection.deletable!(faq)).toBe(true);
    expect(faqCollection.deletable!({ ...faq, pageCount: 2 })).toBe(false);
  });

  it('n’affiche l’arbre qu’en édition', () => {
    const content = faqForm.sections.find((section) => section.fields.some((field) => field.type === 'component'));
    expect(content?.on).toBe('edit');
  });
});

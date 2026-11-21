import { describe, expect, it } from 'vitest';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import tagModule from '@admin/Modules/Tag/module';
import { tagCollection, tagForm } from '@admin/Modules/Tag/screens';

/** Clés du catalogue, une par ligne « clé: valeur » */
const catalogue = (locale: string): Set<string> =>
  new Set(
    readFileSync(resolve(__dirname, `../../../../../../../translations/tag+intl-icu.${locale}.yaml`), 'utf8')
      .split('\n')
      .map((line) => line.split(':')[0])
  );

/** Clés de traduction des descriptions */
function keysOf(): string[] {
  const keys = [
    tagCollection.title,
    tagCollection.description!,
    tagCollection.create!.label,
    ...tagCollection.columns.map((column) => column.label),
    ...Object.values(tagCollection.messages),
    tagForm.title.create,
    tagForm.title.edit,
    tagForm.description!.create,
    tagForm.description!.edit,
    ...Object.values(tagForm.messages),
  ];
  tagForm.sections.forEach((section) => {
    keys.push(section.title);
    section.fields.forEach((field) => {
      if (field.label) keys.push(field.label);
      if (field.help) keys.push(field.help);
    });
  });
  return keys;
}

describe('module Tags', () => {
  it('déclare la liste, la création et l’édition', () => {
    expect(tagModule.id).toBe('tag');
    expect(tagModule.domains).toEqual(['tag']);
    expect(tagModule.routes.map((route) => [route.name, route.path])).toEqual([
      ['tag_index', '/tags'],
      ['tag_new', '/tags/new'],
      ['tag_edit', '/tags/:id(\\d+)'],
    ]);
  });

  it.each(['fr', 'en', 'es'])('traduit chaque clé des écrans en %s', (locale) => {
    const messages = catalogue(locale);
    expect(keysOf().filter((key) => !messages.has(key))).toEqual([]);
  });

  it('ne laisse supprimer en groupe que les tags sans page', () => {
    const tag = { id: 1, label: 'Été', pageCount: 0 } as Parameters<NonNullable<typeof tagCollection.deletable>>[0];
    expect(tagCollection.deletable!(tag)).toBe(true);
    expect(tagCollection.deletable!({ ...tag, pageCount: 2 })).toBe(false);
  });
});

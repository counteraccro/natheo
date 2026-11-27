import { describe, expect, it } from 'vitest';
import commentModule from '@admin/Modules/Comment/module';
import { commentCollection, commentForm } from '@admin/Modules/Comment/screens';
import { COMMENT_STATUSES, commentStatus } from '@/vue/Composables/Admin/Comment/commentStatus';
import type { AdminComment } from '@/ts/Admin/Api/AdminComment.type';
import type { CollectionPatchAction } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormMetaField } from '@/ts/Admin/Screen/FormScreen.type';

const catalogues = import.meta.glob<string>('/translations/comment+intl-icu.*.yaml', {
  eager: true,
  query: '?raw',
  import: 'default',
});

/** Clés du catalogue, une par ligne « clé: valeur » */
const catalogue = (locale: string): Set<string> =>
  new Set(catalogues[`/translations/comment+intl-icu.${locale}.yaml`].split('\n').map((line) => line.split(':')[0]));

const comment = (values: Partial<AdminComment> = {}): AdminComment => ({
  id: 1,
  comment: 'Texte',
  excerpt: 'Texte',
  author: 'Lou',
  email: 'lou@example.com',
  ip: '127.0.0.1',
  userAgent: 'Firefox',
  status: 'wait_validation',
  moderationComment: null,
  moderator: null,
  page: { id: 4, title: 'Accueil', url: '/fr/page/accueil' },
  createdAt: null,
  updatedAt: null,
  permissions: { edit: true, disable: false, delete: false },
  ...values,
});

const patch = (id: string) =>
  commentCollection.bulkActions!.find(
    (action): action is CollectionPatchAction<AdminComment> => action.type === 'patch' && action.id === id
  )!;

/** Clés de traduction des descriptions */
function keysOf(): string[] {
  const keys = [
    commentCollection.title,
    commentCollection.description!,
    ...commentCollection.columns.map((column) => column.label),
    ...Object.values(commentCollection.messages),
    ...(commentCollection.bulkActions ?? []).map((action) => (action.type === 'patch' ? action.label : '')),
    ...COMMENT_STATUSES.map((status) => status.label),
    ...Object.values(commentForm.title),
    ...Object.values(commentForm.description!),
    ...Object.values(commentForm.messages),
    ...(commentForm.meta ?? []).map((meta) => (typeof meta === 'object' ? meta.label : '')),
  ];
  (commentCollection.filters ?? []).forEach((filter) => {
    if (typeof filter === 'object' && filter.type === 'select') keys.push(filter.label, filter.allLabel);
  });
  commentForm.sections.forEach((section) => {
    keys.push(section.title);
    if (section.description) keys.push(section.description);
    section.fields.forEach((field) => {
      if (field.label) keys.push(field.label);
      if (field.help) keys.push(field.help);
    });
  });
  return keys.filter((key) => key !== '');
}

describe('module Commentaires', () => {
  it('déclare la liste et l’édition, sans création, et relit les badges après une écriture', () => {
    expect(commentModule.id).toBe('comment');
    expect(commentModule.domains).toEqual(['comment']);
    expect(commentModule.badges).toEqual(['/admin/comments']);
    expect(commentModule.routes.map((route) => [route.name, route.path])).toEqual([
      ['comment_index', '/comments'],
      ['comment_edit', '/comments/:id(\\d+)'],
    ]);
    expect(commentCollection.create).toBeUndefined();
    expect(commentForm.create).toBeUndefined();
    expect(commentForm.delete).toBeUndefined();
  });

  it.each(['fr', 'en', 'es'])('traduit chaque clé des écrans en %s', (locale) => {
    const messages = catalogue(locale);
    expect(keysOf().filter((key) => !messages.has(key))).toEqual([]);
  });

  it('ne valide ou ne remet en attente que les commentaires qui n’ont pas déjà ce statut', () => {
    expect(patch('validate').payload).toEqual({ status: 'validate' });
    expect(patch('validate').eligible!(comment({ status: 'validate' }))).toBe(false);
    expect(patch('validate').eligible!(comment({ status: 'moderate' }))).toBe(true);
    expect(patch('wait_validation').eligible!(comment())).toBe(false);
    expect(patch('moderate').dialog).toBeDefined();
    expect(patch('moderate').eligible).toBeUndefined();
  });

  it('ne montre le commentaire de modération qu’au statut modéré', () => {
    const field = commentForm.sections[1].fields.find((candidate) => candidate.name === 'moderationComment')!;
    expect(field.visibleWhen!({ status: 'moderate' })).toBe(true);
    expect(field.visibleWhen!({ status: 'validate' })).toBe(false);
  });

  it('lie l’e-mail et la page dans les informations', () => {
    const meta = (name: string) =>
      (commentForm.meta ?? []).find(
        (candidate): candidate is FormMetaField<AdminComment> =>
          typeof candidate === 'object' && candidate.name === name
      )!;
    expect(meta('email').href!(comment())).toBe('mailto:lou@example.com');
    expect(meta('email').href!(comment({ email: null }))).toBeNull();
    expect(meta('page').value(comment())).toBe('Accueil');
    expect(meta('page').href!(comment())).toBe('/fr/page/accueil');
    expect(meta('page').value(comment({ page: null }))).toBeNull();
  });

  it('donne ton et icône de chaque statut, en attente pour un statut inconnu', () => {
    expect(commentStatus('moderate')).toMatchObject({ tone: 'danger', icon: 'ban' });
    expect(commentStatus('inconnu').value).toBe('wait_validation');
  });
});

import { describe, expect, it } from 'vitest';
import { defineCollectionScreen, defineFormScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenDescriptor } from '@/ts/Admin/Screen/FormScreen.type';

const collection: CollectionScreenDescriptor = {
  resource: '/admin/comments',
  read: { operation: 'admin_comment_get_collection' },
  title: 'comment.collection.title',
  columns: [],
  rowLabel: (row) => String(row.id),
  messages: {},
};

const form: FormScreenDescriptor = {
  resource: '/admin/comments',
  read: { operation: 'admin_comment_get' },
  update: { operation: 'admin_comment_patch' },
  title: { create: 'a', edit: 'b', view: 'c' },
  back: 'comment_index',
  leaveKey: 'comment',
  initial: () => ({}),
  sections: [],
  messages: { saved: 'comment.notify.saved' },
  rowLabel: (item) => String(item.id),
};

describe('defineCollectionScreen', () => {
  it('accepte une liste sans création, sans action de ligne ni texte', () => {
    expect(defineCollectionScreen(collection)).toBe(collection);
  });

  it('exige les textes des actions déclarées', () => {
    expect(() => defineCollectionScreen({ ...collection, actions: [{ type: 'toggle', operation: 'x' }] })).toThrow(
      /confirmDisable, disabled, enabled/
    );
    expect(() => defineCollectionScreen({ ...collection, bulkActions: [{ type: 'delete', operation: 'x' }] })).toThrow(
      /confirmDelete, deleted/
    );
  });

  it('refuse deux actions groupées du même identifiant', () => {
    const patch = { type: 'patch', id: 'validate', label: 'l', icon: 'check', operation: 'x' } as const;
    expect(() => defineCollectionScreen({ ...collection, bulkActions: [patch, patch] })).toThrow(/en double/);
  });
});

describe('defineFormScreen', () => {
  it('accepte un formulaire sans création ni suppression', () => {
    expect(defineFormScreen(form)).toBe(form);
  });

  it('exige la route d’édition et les textes de la création et de la suppression', () => {
    expect(() => defineFormScreen({ ...form, create: { operation: 'x' } })).toThrow(/Route d'édition/);
    expect(() => defineFormScreen({ ...form, create: { operation: 'x' }, editRoute: 'comment_edit' })).toThrow(
      /created/
    );
    expect(() => defineFormScreen({ ...form, delete: { operation: 'x' } })).toThrow(/confirmDelete, deleted/);
  });
});

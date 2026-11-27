import { describe, expect, it, vi } from 'vitest';
import { flushPromises } from '@vue/test-utils';
import { createAdminCollection } from '@/vue/Composables/Admin/useAdminCollection';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import * as toast from '@/vue/Composables/Global/toast';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';

type Tag = { id: number; label: string };

function page(members: Tag[], total = members.length, current = 1, lastPage = 1) {
  return { members, total, page: current, lastPage };
}

function collectionWith(getCollection: ReturnType<typeof vi.fn>) {
  return createAdminCollection<Tag>({ getCollection } as unknown as ApiClient, '/admin/tags', {
    itemsPerPage: 10,
    filters: { status: null },
  });
}

describe('createAdminCollection', () => {
  it('envoie la page, le nombre par page, le tri, la recherche et les filtres', async () => {
    const getCollection = vi.fn().mockResolvedValue(page([{ id: 1, label: 'Été' }], 12, 1, 2));
    const collection = collectionWith(getCollection);
    await collection.load();
    expect(getCollection).toHaveBeenLastCalledWith('/admin/tags', {
      page: 1,
      itemsPerPage: 10,
      search: '',
      status: null,
    });
    expect(collection.items.value).toEqual([{ id: 1, label: 'Été' }]);
    expect(collection.total.value).toBe(12);
    expect(collection.lastPage.value).toBe(2);

    collection.sort.value = { key: 'label', direction: 'desc' };
    collection.search.value = ' été ';
    collection.filters.status = 'enabled';
    await flushPromises();
    expect(getCollection).toHaveBeenLastCalledWith('/admin/tags', {
      page: 1,
      itemsPerPage: 10,
      search: 'été',
      'order[label]': 'desc',
      status: 'enabled',
    });
  });

  it('revient à la première page quand le tri change', async () => {
    const getCollection = vi.fn().mockResolvedValue(page([{ id: 1, label: 'Été' }], 30, 1, 3));
    const collection = collectionWith(getCollection);
    collection.page.value = 3;
    await flushPromises();
    collection.sort.value = { key: 'label', direction: 'asc' };
    await flushPromises();
    expect(collection.page.value).toBe(1);
    expect(getCollection.mock.lastCall?.[1]).toMatchObject({ page: 1, 'order[label]': 'asc' });
  });

  it('ignore une réponse arrivée après une requête plus récente', async () => {
    let resolveFirst: (value: unknown) => void = () => {};
    const getCollection = vi
      .fn()
      .mockReturnValueOnce(new Promise((resolve) => (resolveFirst = resolve)))
      .mockResolvedValueOnce(page([{ id: 2, label: 'Récent' }]));
    const collection = collectionWith(getCollection);
    const first = collection.load();
    await collection.load();
    resolveFirst(page([{ id: 1, label: 'Ancien' }]));
    await first;
    expect(collection.items.value).toEqual([{ id: 2, label: 'Récent' }]);
    expect(collection.loading.value).toBe(false);
  });

  it('revient à la dernière page quand la page courante a été vidée', async () => {
    const getCollection = vi
      .fn()
      .mockResolvedValueOnce(page([], 21, 4, 3))
      .mockResolvedValueOnce(page([{ id: 21, label: 'Dernier' }], 21, 3, 3));
    const collection = collectionWith(getCollection);
    collection.page.value = 4;
    await flushPromises();
    expect(collection.page.value).toBe(3);
    expect(collection.items.value).toEqual([{ id: 21, label: 'Dernier' }]);
  });

  it('garde l’erreur et la notifie', async () => {
    const notify = vi.spyOn(toast, 'notify');
    const collection = collectionWith(
      vi.fn().mockRejectedValue(new ApiError(403, 'Accès refusé', 'Droits insuffisants'))
    );
    await collection.load();
    expect(collection.error.value?.status).toBe(403);
    expect(notify).toHaveBeenCalledWith('danger', 'Accès refusé', 'Droits insuffisants');
    expect(collection.loading.value).toBe(false);
  });
});

import { describe, expect, it, vi } from 'vitest';
import { flushPromises } from '@vue/test-utils';
import { createAdminCollection, SELECT_ALL_LIMIT } from '@/vue/Composables/Admin/useAdminCollection';
import { createCollectionScreen, runPool } from '@/vue/Composables/Admin/Screen/useCollectionScreen';
import { defineCollectionScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import * as toast from '@/vue/Composables/Global/toast';
import { onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';

type Tag = {
  id: number;
  label: string;
  disabled: boolean;
  pageCount: number;
  permissions: { edit: boolean; disable: boolean; delete: boolean };
};

const all = { edit: true, disable: true, delete: true };
const tag = (id: number, values: Partial<Tag> = {}): Tag => ({
  id,
  label: 'Tag ' + id,
  disabled: false,
  pageCount: 0,
  permissions: all,
  ...values,
});

const descriptor = defineCollectionScreen<Tag>({
  resource: '/admin/tags',
  title: 'tag.index.title',
  columns: [
    { key: 'id', label: 'tag.index.column_id', sortable: true },
    { key: 'pageCount', label: 'tag.index.column_pages', type: 'number' },
  ],
  create: { route: 'tag_new', label: 'tag.index.button_new', operation: 'admin_tag_post' },
  actions: [
    { type: 'edit', route: 'tag_edit', operation: 'admin_tag_patch' },
    { type: 'toggle', operation: 'admin_tag_patch' },
    { type: 'delete', operation: 'admin_tag_delete' },
  ],
  bulkActions: [
    { type: 'disable', operation: 'admin_tag_patch' },
    { type: 'delete', operation: 'admin_tag_delete' },
  ],
  rowLabel: (row) => row.label,
  deletable: (row) => row.pageCount === 0,
  messages: {
    confirmDisable: 'tag.confirm.disable',
    confirmDelete: 'tag.confirm.delete',
    disabled: 'tag.notify.disabled',
    enabled: 'tag.notify.enabled',
    deleted: 'tag.notify.deleted',
  },
});

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

function canWith(operations: string[]): AdminCan {
  return {
    canOperation: (name) => operations.includes(name),
    can: (action, operation, item) => operations.includes(operation) && item?.permissions?.[action] !== false,
    hasRole: () => false,
  };
}

function screenWith(rows: Tag[], options: { operations?: string[]; confirm?: boolean; api?: object } = {}) {
  const api = {
    getCollection: vi.fn().mockResolvedValue({ members: rows, total: rows.length, page: 1, lastPage: 1 }),
    patch: vi.fn().mockResolvedValue({}),
    delete: vi.fn().mockResolvedValue(undefined),
    post: vi.fn().mockResolvedValue({}),
    ...options.api,
  };
  const client = api as unknown as ApiClient;
  const collection = createAdminCollection<Tag>(client, '/admin/tags', { itemsPerPage: 10 });
  const confirm = vi.fn().mockResolvedValue(options.confirm ?? true);
  const router = { push: vi.fn(), resolve: vi.fn((to: { name: string }) => ({ href: `/admin/fr/${to.name}` })) };
  const screen = createCollectionScreen(descriptor, {
    api: client,
    t,
    can: canWith(options.operations ?? ['admin_tag_post', 'admin_tag_patch', 'admin_tag_delete']),
    confirm,
    router,
    collection,
    locale: 'fr',
  });
  return { screen, api, confirm, router, collection };
}

describe('createCollectionScreen', () => {
  it('traduit les colonnes et montre les actions permises par les droits de chaque ligne', () => {
    const { screen } = screenWith([]);
    expect(screen.columns.map((column) => [column.label, column.align])).toEqual([
      ['tag.index.column_id', undefined],
      ['tag.index.column_pages', 'end'],
    ]);
    expect(screen.canCreate).toBe(true);
    expect(screen.canSql).toBe(false);
    expect(screen.actionsFor(tag(1)).map((action) => action.id)).toEqual(['edit', 'disable', 'delete']);
    expect(screen.actionsFor(tag(2, { disabled: true })).map((action) => action.id)).toEqual([
      'edit',
      'enable',
      'delete',
    ]);
    const readOnly = tag(3, { permissions: { edit: false, disable: false, delete: false } });
    expect(screen.actionsFor(readOnly)).toEqual([]);
  });

  it('cache ce que les opérations du module ne permettent pas', () => {
    const { screen } = screenWith([], { operations: ['admin_tag_patch', 'admin_sql_query_post'] });
    expect(screen.canCreate).toBe(false);
    expect(screen.canSql).toBe(true);
    expect(screen.actionsFor(tag(1)).map((action) => action.id)).toEqual(['edit', 'disable']);
    expect(screen.bulkActions.map((action) => action.id)).toEqual(['disable']);
  });

  it("ouvre l'édition, confirme la désactivation et active sans confirmation", async () => {
    const notify = vi.spyOn(toast, 'notify');
    const { screen, api, confirm, router } = screenWith([tag(1)]);
    await screen.runAction('edit', tag(1));
    expect(router.push).toHaveBeenCalledWith({ name: 'tag_edit', params: { id: '1' } });

    await screen.runAction('disable', tag(1));
    expect(confirm).toHaveBeenCalledTimes(1);
    expect(api.patch).toHaveBeenLastCalledWith('/admin/tags/1', { disabled: true });
    expect(notify).toHaveBeenLastCalledWith('success', 'tag.notify.disabled {"label":"Tag 1"}');

    await screen.runAction('enable', tag(2, { disabled: true }));
    expect(confirm).toHaveBeenCalledTimes(1);
    expect(api.patch).toHaveBeenLastCalledWith('/admin/tags/2', { disabled: false });
  });

  it("ne supprime pas sans confirmation et montre l'erreur de l'API", async () => {
    const notify = vi.spyOn(toast, 'notify');
    const refused = new ApiError(409, 'Conflit', 'Tag utilisé', null);
    const { screen, api } = screenWith([tag(1)], { confirm: false });
    await screen.runAction('delete', tag(1));
    expect(api.delete).not.toHaveBeenCalled();

    const other = screenWith([tag(1)], { api: { delete: vi.fn().mockRejectedValue(refused) } });
    await other.screen.runAction('delete', tag(1));
    expect(notify).toHaveBeenLastCalledWith('danger', 'Conflit', 'Tag utilisé');
  });

  it('supprime en groupe les éléments supprimables et résume le résultat', async () => {
    const notify = vi.spyOn(toast, 'notify');
    const rows = [tag(1), tag(2, { pageCount: 3 }), tag(3), tag(4, { permissions: { ...all, delete: false } })];
    const { screen, api, confirm, collection } = screenWith(rows, {
      api: {
        delete: vi
          .fn()
          .mockImplementation((path: string) =>
            path.endsWith('/3') ? Promise.reject(new Error('412')) : Promise.resolve()
          ),
      },
    });
    await collection.load();
    collection.selected.value = [1, 2, 3, 4];

    const result = await screen.runBulk('delete', [1, 2, 3, 4]);
    expect(confirm.mock.lastCall?.[0]).toMatchObject({ title: 'admin_app.screen.bulk_delete_title {"count":2}' });
    expect(api.delete).toHaveBeenCalledTimes(2);
    expect(result).toEqual({ done: 1, failed: 3 });
    expect(notify).toHaveBeenLastCalledWith('warning', 'admin_app.screen.bulk_result {"done":1,"failed":3}');
    expect(collection.selected.value).toEqual([]);
  });

  it('annonce ses écritures aux formulaires ouverts, éléments touchés et suppression', async () => {
    const changed = vi.fn();
    const stop = onResourceChange('/admin/tags', changed);
    const rows = [tag(1), tag(2), tag(3)];
    const { screen, collection } = screenWith(rows, {
      api: {
        delete: vi
          .fn()
          .mockImplementation((path: string) =>
            path.endsWith('/3') ? Promise.reject(new Error('412')) : Promise.resolve()
          ),
      },
    });
    await collection.load();

    await screen.runAction('disable', tag(1));
    expect(changed).toHaveBeenLastCalledWith({ ids: [1], deleted: false, source: collection });
    await screen.runAction('delete', tag(2));
    expect(changed).toHaveBeenLastCalledWith({ ids: [2], deleted: true, source: collection });
    await screen.runBulk('delete', [1, 3]);
    expect(changed).toHaveBeenLastCalledWith({ ids: [1], deleted: true, source: collection });
    stop();
  });

  it('ne désactive en groupe que les éléments encore actifs', async () => {
    const { screen, api, collection } = screenWith([tag(1), tag(2, { disabled: true })]);
    await collection.load();
    await screen.runBulk('disable', [1, 2]);
    expect(api.patch).toHaveBeenCalledTimes(1);
    expect(api.patch).toHaveBeenCalledWith('/admin/tags/1', { disabled: true });
  });

  it('lie les filtres génériques à la collection', async () => {
    const { screen, api } = screenWith([]);
    screen.filter.status.value = 'disabled';
    screen.filter.author.value = 'me';
    await flushPromises();
    expect(api.getCollection.mock.lastCall?.[1]).toMatchObject({ status: 'disabled', author: 'me' });
    screen.filter.author.value = '';
    await flushPromises();
    expect(api.getCollection.mock.lastCall?.[1].author).toBeUndefined();
  });

  it('enregistre la requête SQL dans le gestionnaire', async () => {
    const { screen, api } = screenWith([]);
    await screen.saveSql('SELECT 1');
    expect(api.post).toHaveBeenCalledWith('/admin/sql_queries', { query: 'SELECT 1' });
    expect(screen.savedSql.value).toBe('SELECT 1');
  });

  it("n'enregistre qu'une fois la même requête, même sur un double clic", async () => {
    const { screen, api } = screenWith([]);
    await Promise.all([screen.saveSql('SELECT 1'), screen.saveSql('SELECT 1')]);
    await screen.saveSql('SELECT 1');
    expect(api.post).toHaveBeenCalledTimes(1);
    await screen.saveSql('SELECT 2');
    expect(api.post).toHaveBeenCalledTimes(2);
  });
});

describe('selectAll', () => {
  it('lit toute la collection page par page avec les mêmes filtres, plafonnée', async () => {
    const members = (from: number) => Array.from({ length: 100 }, (_, i) => tag(from + i));
    const getCollection = vi.fn((_path: string, query: Record<string, unknown>) =>
      Promise.resolve({ members: members(((query.page as number) - 1) * 100), total: 1500, page: 1, lastPage: 15 })
    );
    const collection = createAdminCollection<Tag>({ getCollection } as unknown as ApiClient, '/admin/tags', {
      filters: { status: 'enabled' },
      withSql: true,
    });
    expect(await collection.selectAll()).toBe(true);
    expect(collection.selected.value).toHaveLength(SELECT_ALL_LIMIT);
    expect(getCollection).toHaveBeenCalledTimes(10);
    expect(getCollection.mock.lastCall?.[1]).toMatchObject({ page: 10, itemsPerPage: 100, status: 'enabled' });
    expect(getCollection.mock.lastCall?.[1].sql).toBeUndefined();
    expect(collection.known.get(999)).toMatchObject({ id: 999 });
  });
});

describe('runPool', () => {
  it('ne lance pas plus de requêtes à la fois que demandé', async () => {
    let running = 0;
    let max = 0;
    const result = await runPool([1, 2, 3, 4, 5, 6, 7], 3, async (n) => {
      running++;
      max = Math.max(max, running);
      await new Promise((resolve) => setTimeout(resolve, 1));
      running--;
      if (n === 7) throw new Error('refusé');
    });
    expect(max).toBe(3);
    expect(result).toEqual({ done: 6, failed: 1 });
  });
});

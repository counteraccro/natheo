/**
 * @author Gourdon Aymeric
 * @version 1.2
 * État et actions d'un écran de liste décrit par des données : colonnes, actions visibles selon les droits,
 * confirmations, requêtes, notifications et actions groupées (une requête par élément, quatre à la fois)
 */
import { computed, onScopeDispose, ref, type Ref } from 'vue';
import { useRouter, type Router } from 'vue-router';
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm, type ConfirmRequest } from '@/vue/Composables/Admin/useConfirm';
import { formatDateTime } from '@/vue/Composables/Admin/Screen/screenFormat';
import { notifyResourceChange, onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import {
  createAdminCollection,
  SELECT_ALL_LIMIT,
  type AdminCollection,
} from '@/vue/Composables/Admin/useAdminCollection';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';
import type { Translator } from '@/vue/Composables/Admin/useTranslate';
import type { DataTableAction } from '@/ts/Admin/Ui/Data/UiDataTable.type';
import type {
  BulkResult,
  CollectionColumn,
  CollectionFilter,
  CollectionRowAction,
  CollectionScreenDescriptor,
  CollectionScreenState,
} from '@/ts/Admin/Screen/CollectionScreen.type';
import type { ScreenAuthor, ScreenItem } from '@/ts/Admin/Screen/Screen.type';

/** Requêtes lancées en même temps par une action groupée */
export const BULK_CONCURRENCY = 4;

/** Opération qui enregistre une requête dans le gestionnaire SQL */
export const SQL_QUERY_OPERATION = 'admin_sql_query_post';

export interface CollectionScreenDeps<T> {
  api: ApiClient;
  t: Translator['t'];
  can: AdminCan;
  confirm: (request: ConfirmRequest) => Promise<boolean>;
  router: Pick<Router, 'push'> & { resolve(to: { name: string }): { href: string } };
  collection: AdminCollection<T>;
  /** Langue de l'interface, pour les dates */
  locale: string;
}

/**
 * Exécute fn sur chaque élément, au plus concurrency à la fois
 * @param items
 * @param concurrency
 * @param fn
 * @return nombre de réussites et d'échecs
 */
export async function runPool<I>(
  items: I[],
  concurrency: number,
  fn: (item: I) => Promise<unknown>
): Promise<BulkResult> {
  const result: BulkResult = { done: 0, failed: 0 };
  let next = 0;
  const worker = async (): Promise<void> => {
    while (next < items.length) {
      const item = items[next++];
      try {
        await fn(item);
        result.done++;
      } catch {
        result.failed++;
      }
    }
  };
  await Promise.all(Array.from({ length: Math.min(concurrency, items.length) }, worker));
  return result;
}

/**
 * Crée l'état de l'écran
 * @param descriptor
 * @param deps
 */
export function createCollectionScreen<T extends ScreenItem>(
  descriptor: CollectionScreenDescriptor<T>,
  deps: CollectionScreenDeps<T>
): CollectionScreenState<T> {
  const { api, t, can, confirm, router, collection, locale } = deps;
  const itemPath = (row: T): string => `${descriptor.resource}/${row.id}`;
  const actionOf = <K extends CollectionRowAction['type']>(type: K) =>
    descriptor.actions?.find((action) => action.type === type) as Extract<CollectionRowAction, { type: K }> | undefined;
  const label = (row: T): string => descriptor.rowLabel(row);
  const savedSql = ref<string | null>(null);
  let savingSql: string | null = null;

  // Annonce l'écriture aux formulaires ouverts ; la liste s'ignore et se recharge elle-même
  const announce = (ids: (string | number)[], deleted: boolean): void =>
    notifyResourceChange(descriptor.resource, { ids, deleted, source: collection });

  const fail = (caught: unknown): void => {
    const error = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
    notify('danger', error.title, error.detail ?? undefined);
  };

  const filterRef = (name: CollectionFilter): Ref<string> =>
    computed({
      get: () => String(collection.filters[name] ?? ''),
      set: (value: string) => {
        collection.filters[name] = value === '' ? undefined : value;
      },
    });

  const actionsFor = (row: T): DataTableAction[] => {
    const actions: DataTableAction[] = [];
    const edit = actionOf('edit');
    if (edit) {
      actions.push(
        can.can('edit', edit.operation, row)
          ? { id: 'edit', label: t('admin_app.screen.action_edit'), icon: 'edit' }
          : { id: 'view', label: t('admin_app.screen.action_view'), icon: 'eye' }
      );
    }
    const toggle = actionOf('toggle');
    if (toggle && can.can('disable', toggle.operation, row)) {
      actions.push(
        row.disabled
          ? { id: 'enable', label: t('admin_app.screen.action_enable'), icon: 'check-circle' }
          : { id: 'disable', label: t('admin_app.screen.action_disable'), icon: 'ban' }
      );
    }
    const remove = actionOf('delete');
    if (remove && can.can('delete', remove.operation, row)) {
      actions.push({ id: 'delete', label: t('admin_app.screen.action_delete'), icon: 'trash', danger: true });
    }
    return actions;
  };

  const setDisabled = async (row: T, disabled: boolean): Promise<void> => {
    if (
      disabled &&
      !(await confirm({
        title: t(descriptor.messages.confirmDisable, { label: label(row) }),
        icon: 'ban',
        confirmLabel: t('admin_app.screen.action_disable'),
        confirmIcon: 'ban',
      }))
    ) {
      return;
    }
    try {
      await api.patch(itemPath(row), { disabled });
      announce([row.id], false);
      notify(
        'success',
        t(disabled ? descriptor.messages.disabled : descriptor.messages.enabled, { label: label(row) })
      );
      await collection.load();
    } catch (caught) {
      fail(caught);
    }
  };

  const remove = async (row: T): Promise<void> => {
    const confirmed = await confirm({
      title: t(descriptor.messages.confirmDelete, { label: label(row) }),
      message: t('admin_app.screen.confirm_irreversible'),
      tone: 'danger',
      icon: 'trash',
      confirmLabel: t('admin_app.screen.action_delete'),
      confirmIcon: 'trash',
    });
    if (!confirmed) return;
    try {
      await api.delete(itemPath(row));
      announce([row.id], true);
      notify('success', t(descriptor.messages.deleted, { label: label(row) }));
      collection.selected.value = collection.selected.value.filter((key) => key !== row.id);
      await collection.load();
    } catch (caught) {
      fail(caught);
    }
  };

  const bulkActions: DataTableAction[] = (descriptor.bulkActions ?? [])
    .filter((action) => can.canOperation(action.operation))
    .map((action) => ({
      id: action.type,
      label:
        action.type === 'delete'
          ? t('admin_app.screen.action_delete')
          : action.type === 'disable'
            ? t('admin_app.screen.action_disable')
            : t('admin_app.screen.action_enable'),
      icon: action.type === 'delete' ? 'trash' : action.type === 'disable' ? 'ban' : 'check-circle',
      danger: action.type === 'delete',
    }));

  const runBulk = async (id: string, keys: (string | number)[]): Promise<BulkResult | null> => {
    const action = descriptor.bulkActions?.find((candidate) => candidate.type === id);
    if (!action) return null;
    const rows = keys.map((key) => collection.known.get(key)).filter((row): row is T => row !== undefined);
    const isDelete = action.type === 'delete';
    const eligible = rows.filter((row) =>
      isDelete
        ? can.can('delete', action.operation, row) && (descriptor.deletable?.(row) ?? true)
        : can.can('disable', action.operation, row) && !!row.disabled !== (action.type === 'disable')
    );
    const kept = keys.length - eligible.length;
    if (eligible.length === 0) {
      notify('warning', t('admin_app.screen.bulk_none'));
      return null;
    }
    if (action.type !== 'enable') {
      const confirmed = await confirm({
        title: isDelete
          ? t('admin_app.screen.bulk_delete_title', { count: eligible.length })
          : t('admin_app.screen.bulk_disable_title', { count: eligible.length }),
        message: t('admin_app.screen.bulk_kept', { kept }),
        tone: isDelete ? 'danger' : undefined,
        icon: isDelete ? 'trash' : 'ban',
        confirmLabel: isDelete ? t('admin_app.screen.action_delete') : t('admin_app.screen.action_disable'),
        confirmIcon: isDelete ? 'trash' : 'ban',
      });
      if (!confirmed) return null;
    }

    const written: (string | number)[] = [];
    const result = await runPool(eligible, BULK_CONCURRENCY, async (row) => {
      await (isDelete ? api.delete(itemPath(row)) : api.patch(itemPath(row), { disabled: action.type === 'disable' }));
      written.push(row.id);
    });
    if (written.length > 0) announce(written, isDelete);
    result.failed += kept;
    notify(result.failed > 0 ? 'warning' : 'success', t('admin_app.screen.bulk_result', { ...result }));
    collection.selected.value = [];
    await collection.load();
    return result;
  };

  return {
    descriptor,
    collection,
    columns: descriptor.columns.map((column) => ({
      key: column.key,
      label: t(column.label),
      sortable: column.sortable,
      hidden: column.hidden,
      hideBelow: column.hideBelow,
      align: column.type === 'number' ? 'end' : undefined,
    })),
    canCreate: descriptor.create !== undefined && can.canOperation(descriptor.create.operation),
    createHref: descriptor.create ? router.resolve({ name: descriptor.create.route }).href : '',
    statusOptions: [
      { value: '', label: t('admin_app.screen.filter_status_all') },
      { value: 'enabled', label: t('admin_app.screen.filter_status_enabled') },
      { value: 'disabled', label: t('admin_app.screen.filter_status_disabled') },
    ],
    authorOptions: [
      { value: '', label: t('admin_app.screen.filter_author_all') },
      { value: 'me', label: t('admin_app.screen.filter_author_me') },
    ],
    cellText(column: CollectionColumn, row: T): string {
      const value = (row as unknown as Record<string, unknown>)[column.key];
      if (column.type === 'author') return (value as ScreenAuthor | null)?.login ?? '—';
      if (column.type === 'date') return formatDateTime(value as string | null, locale);
      return String(value ?? '');
    },
    canSql: can.canOperation(SQL_QUERY_OPERATION),
    bulkActions,
    actionsFor,
    async runAction(id: string, row: T): Promise<void> {
      if (id === 'edit' || id === 'view') {
        const edit = actionOf('edit');
        if (edit) await router.push({ name: edit.route, params: { id: String(row.id) } });
      } else if (id === 'disable' || id === 'enable') {
        await setDisabled(row, id === 'disable');
      } else if (id === 'delete') {
        await remove(row);
      }
    },
    runBulk,
    async selectAll(): Promise<void> {
      try {
        if (await collection.selectAll()) {
          notify('info', t('admin_app.screen.select_all_limit', { limit: SELECT_ALL_LIMIT }));
        }
      } catch (caught) {
        fail(caught);
      }
    },
    savedSql,
    async saveSql(sql: string): Promise<void> {
      if (savedSql.value === sql || savingSql === sql) return;
      savingSql = sql;
      try {
        await api.post('/admin/sql_queries', { query: sql });
        savedSql.value = sql;
        notify('success', t('admin_app.screen.sql_saved'));
      } catch (caught) {
        fail(caught);
      } finally {
        savingSql = null;
      }
    },
    filter: { status: filterRef('status'), author: filterRef('author') },
  };
}

/**
 * État d'un écran de liste, avec le nombre par page de l'utilisateur
 * @param descriptor
 */
export function useCollectionScreen<T extends ScreenItem>(
  descriptor: CollectionScreenDescriptor<T>
): CollectionScreenState<T> {
  const { api, t, can, bootstrap } = useAdminContext();
  const collection = createAdminCollection<T>(api, descriptor.resource, {
    itemsPerPage: bootstrap.options.itemsPerPage,
    withSql: can.canOperation(SQL_QUERY_OPERATION),
  });
  // Un formulaire affiché à côté de la liste l'a modifiée
  onScopeDispose(
    onResourceChange(descriptor.resource, (change) => {
      if (change.source !== collection) void collection.load();
    })
  );
  return createCollectionScreen(descriptor, {
    api,
    t,
    can,
    confirm: useConfirm().confirm,
    router: useRouter(),
    collection,
    locale: bootstrap.locale,
  });
}

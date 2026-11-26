/**
 * @author Gourdon Aymeric
 * @version 1.4
 * État et actions d'un écran de liste décrit par des données : colonnes, filtres, recherche, page et tri gardés dans
 * l'adresse et repris par les liens vers ses éléments, actions visibles selon les droits, confirmations, requêtes,
 * notifications et actions groupées (une requête par élément, quatre à la fois, dialogue du module)
 */
import { computed, nextTick, onScopeDispose, ref, shallowRef, watch, type Ref } from 'vue';
import { useRoute, useRouter, type LocationQuery, type RouteLocationNormalizedLoaded, type Router } from 'vue-router';
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm, type ConfirmRequest } from '@/vue/Composables/Admin/useConfirm';
import { formatDateTime } from '@/vue/Composables/Admin/Screen/screenFormat';
import { toComponent } from '@/vue/Composables/Admin/Screen/adminTheme';
import { notifyResourceChange, onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import {
  createAdminCollection,
  SELECT_ALL_LIMIT,
  type AdminCollection,
  type CollectionFilterValue,
} from '@/vue/Composables/Admin/useAdminCollection';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';
import type { Translator } from '@/vue/Composables/Admin/useTranslate';
import type { DataTableAction, DataTableSort } from '@/ts/Admin/Ui/Data/UiDataTable.type';
import type {
  BulkDialogRequest,
  BulkResult,
  CollectionColumn,
  CollectionMessages,
  CollectionPatchAction,
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
  router: Pick<Router, 'push'> & { resolve(to: { name: string; query?: LocationQuery }): { href: string } };
  collection: AdminCollection<T>;
  /** Langue de l'interface, pour les dates */
  locale: string;
  /** Terme saisi dans la recherche, gardé dans l'adresse */
  term?: Ref<string>;
  /** Adresse de la liste, reprise par les liens vers ses éléments pour la retrouver au retour */
  listQuery?: () => LocationQuery;
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
 * Paramètres des filtres déclarés (status, author, moderation…)
 * @param descriptor
 */
export function filterParams(descriptor: CollectionScreenDescriptor<never>): string[] {
  return (descriptor.filters ?? []).map((filter) => (typeof filter === 'string' ? filter : filter.param));
}

/**
 * Valeurs des filtres lues dans l'adresse, vides ignorées
 * @param query
 * @param params
 */
export function filtersFromQuery(query: LocationQuery, params: string[]): Record<string, CollectionFilterValue> {
  return Object.fromEntries(
    params.map((param) => {
      const value = query[param];
      return [param, typeof value === 'string' && value !== '' ? value : undefined];
    })
  );
}

/** État d'une liste gardé dans son adresse */
export interface ListState {
  filters: Record<string, CollectionFilterValue>;
  search: string;
  page: number;
  sort: DataTableSort | null;
  itemsPerPage: number;
}

const ORDER_KEY = /^order\[(.+)\]$/;

/**
 * État de la liste lu dans l'adresse : filtres, recherche, page, tri (order[champ]) et nombre par page
 * @param query
 * @param params paramètres des filtres
 * @param itemsPerPage nombre par page de l'utilisateur, absent de l'adresse
 */
export function listStateFromQuery(query: LocationQuery, params: string[], itemsPerPage: number): ListState {
  const text = (key: string): string => (typeof query[key] === 'string' ? (query[key] as string) : '');
  const positive = (key: string, fallback: number): number => {
    const value = Number.parseInt(text(key), 10);
    return Number.isInteger(value) && value > 0 ? value : fallback;
  };
  let sort: DataTableSort | null = null;
  for (const [key, value] of Object.entries(query)) {
    const match = ORDER_KEY.exec(key);
    if (match && (value === 'asc' || value === 'desc')) sort = { key: match[1], direction: value };
  }
  return {
    filters: filtersFromQuery(query, params),
    search: text('search').trim(),
    page: positive('page', 1),
    sort,
    itemsPerPage: positive('itemsPerPage', itemsPerPage),
  };
}

/**
 * Adresse de la liste : celle reçue, dont les clés de la liste sont remplacées par son état, valeurs par défaut omises
 * @param state
 * @param params paramètres des filtres
 * @param itemsPerPage nombre par page de l'utilisateur
 * @param base adresse courante
 */
export function listStateToQuery(
  state: ListState,
  params: string[],
  itemsPerPage: number,
  base: LocationQuery = {}
): LocationQuery {
  const query: LocationQuery = Object.fromEntries(
    Object.entries(base).filter(
      ([key]) => !params.includes(key) && !['search', 'page', 'itemsPerPage'].includes(key) && !ORDER_KEY.test(key)
    )
  );
  params.forEach((param) => {
    const value = state.filters[param];
    if (value !== undefined && value !== null && value !== '') query[param] = String(value);
  });
  if (state.search !== '') query.search = state.search;
  if (state.sort) query[`order[${state.sort.key}]`] = state.sort.direction;
  if (state.page > 1) query.page = String(state.page);
  if (state.itemsPerPage !== itemsPerPage) query.itemsPerPage = String(state.itemsPerPage);
  return query;
}

/**
 * État courant d'une liste
 * @param collection
 * @param term terme saisi dans la recherche
 * @param params paramètres des filtres
 */
function listStateOf(collection: AdminCollection<unknown>, term: string, params: string[]): ListState {
  return {
    filters: Object.fromEntries(params.map((param) => [param, collection.filters[param]])),
    search: term.trim(),
    page: collection.page.value,
    sort: collection.sort.value,
    itemsPerPage: collection.itemsPerPage.value,
  };
}

/**
 * Lie l'état de la liste à l'adresse des routes de son groupe (liste, formulaire ouvert à côté) : relu quand
 * l'adresse change (entrée de menu, lien, retour), écrit dedans à chaque changement, pour le retrouver au retour
 * @param collection
 * @param term terme saisi dans la recherche
 * @param params paramètres des filtres
 * @param route route courante, réactive
 * @param router
 * @param listName nom de la route de la liste
 * @param itemsPerPage nombre par page de l'utilisateur
 * @return arrêt du lien
 */
export function bindListQuery(
  collection: AdminCollection<unknown>,
  term: Ref<string>,
  params: string[],
  route: RouteLocationNormalizedLoaded,
  router: Pick<Router, 'replace'>,
  listName: string,
  itemsPerPage: number
): () => void {
  const inGroup = (): boolean => route.name === listName || route.meta.screenGroup === listName;
  // Écritures en cours : leur adresse, plus ancienne que l'état, n'est pas relue
  let writing = 0;
  const same = (a: unknown, b: unknown): boolean => JSON.stringify(a) === JSON.stringify(b);

  const stopRoute = watch(
    () => (inGroup() ? JSON.stringify(listStateFromQuery(route.query, params, itemsPerPage)) : null),
    async (key) => {
      if (key === null || writing > 0) return;
      const state = JSON.parse(key) as ListState;
      const before = listStateOf(collection, term.value, params);
      params.forEach((param) => {
        const value = state.filters[param] ?? undefined;
        if (collection.filters[param] !== value) collection.filters[param] = value;
      });
      if (state.search !== before.search) {
        term.value = state.search;
        collection.search.value = state.search;
      }
      if (!same(state.sort, before.sort)) collection.sort.value = state.sort;
      if (state.itemsPerPage !== before.itemsPerPage) collection.itemsPerPage.value = state.itemsPerPage;
      if (state.page === collection.page.value) return;
      // Un filtre, la recherche ou le tri changés remettent la collection en page 1 : la page vient après
      const reset =
        !same(state.filters, before.filters) || state.search !== before.search || !same(state.sort, before.sort);
      if (reset) await nextTick();
      collection.page.value = state.page;
    }
  );
  const stopState = watch(
    () => listStateOf(collection, term.value, params),
    (state) => {
      if (!inGroup()) return;
      const query = listStateToQuery(state, params, itemsPerPage, route.query);
      if (
        !same(
          query,
          listStateToQuery(listStateFromQuery(route.query, params, itemsPerPage), params, itemsPerPage, route.query)
        )
      ) {
        writing++;
        void Promise.resolve(router.replace({ query })).finally(() => writing--);
      }
    },
    { deep: true }
  );
  return () => {
    stopRoute();
    stopState();
  };
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
  const term = deps.term ?? ref(collection.search.value);
  const listQuery = deps.listQuery ?? (() => ({}));
  const itemPath = (row: T): string => `${descriptor.resource}/${row.id}`;
  const actionOf = <K extends CollectionRowAction['type']>(type: K) =>
    descriptor.actions?.find((action) => action.type === type) as Extract<CollectionRowAction, { type: K }> | undefined;
  const label = (row: T): string => descriptor.rowLabel(row);
  // Textes exigés par defineCollectionScreen quand leur action est déclarée
  const message = (key: keyof CollectionMessages, row: T): string =>
    t(descriptor.messages[key] ?? '', { label: label(row) });
  const patchActions = (descriptor.bulkActions ?? []).filter(
    (action): action is CollectionPatchAction<T> => action.type === 'patch'
  );
  const dialogs = new Map(
    patchActions.filter((action) => action.dialog).map((action) => [action.id, toComponent(action.dialog!)])
  );
  const bulkDialog = shallowRef<BulkDialogRequest | null>(null);
  let answerDialog: ((payload: Record<string, unknown> | null) => void) | null = null;
  const savedSql = ref<string | null>(null);
  let savingSql: string | null = null;

  // Annonce l'écriture aux formulaires ouverts ; la liste s'ignore et se recharge elle-même
  const announce = (ids: (string | number)[], deleted: boolean): void =>
    notifyResourceChange(descriptor.resource, { ids, deleted, source: collection });

  const fail = (caught: unknown): void => {
    const error = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
    notify('danger', error.title, error.detail ?? undefined);
  };

  const filterRef = (name: string): Ref<string> =>
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
        title: message('confirmDisable', row),
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
      notify('success', message(disabled ? 'disabled' : 'enabled', row));
      await collection.load();
    } catch (caught) {
      fail(caught);
    }
  };

  const remove = async (row: T): Promise<void> => {
    const confirmed = await confirm({
      title: message('confirmDelete', row),
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
      notify('success', message('deleted', row));
      collection.selected.value = collection.selected.value.filter((key) => key !== row.id);
      await collection.load();
    } catch (caught) {
      fail(caught);
    }
  };

  const bulkActions: DataTableAction[] = (descriptor.bulkActions ?? [])
    .filter((action) => can.canOperation(action.operation))
    .map((action) =>
      action.type === 'patch'
        ? { id: action.id, label: t(action.label), icon: action.icon }
        : {
            id: action.type,
            label:
              action.type === 'delete'
                ? t('admin_app.screen.action_delete')
                : action.type === 'disable'
                  ? t('admin_app.screen.action_disable')
                  : t('admin_app.screen.action_enable'),
            icon: action.type === 'delete' ? 'trash' : action.type === 'disable' ? 'ban' : 'check-circle',
            danger: action.type === 'delete',
          }
    );

  /**
   * Ouvre le dialogue d'une action groupée
   * @return corps saisi, null si le dialogue est annulé
   */
  const askDialog = (id: string, count: number, kept: number): Promise<Record<string, unknown> | null> =>
    new Promise((resolve) => {
      answerDialog?.(null);
      answerDialog = resolve;
      bulkDialog.value = { component: dialogs.get(id)!, count, kept };
    });

  /** Modification groupée du module : un PATCH par élément modifiable et concerné */
  const runPatch = async (action: CollectionPatchAction<T>, rows: T[], total: number): Promise<BulkResult | null> => {
    const eligible = rows.filter((row) => can.can('edit', action.operation, row) && (action.eligible?.(row) ?? true));
    const kept = total - eligible.length;
    if (eligible.length === 0) {
      notify('warning', t('admin_app.screen.bulk_none'));
      return null;
    }
    let body: Record<string, unknown> = { ...action.payload };
    if (dialogs.has(action.id)) {
      const answer = await askDialog(action.id, eligible.length, kept);
      if (answer === null) return null;
      body = { ...body, ...answer };
    } else if (action.confirm) {
      const confirmed = await confirm({
        title: t(action.confirm, { count: eligible.length }),
        message: t('admin_app.screen.bulk_kept', { kept }),
        icon: action.icon,
        confirmLabel: t(action.label),
        confirmIcon: action.icon,
      });
      if (!confirmed) return null;
    }
    const written: (string | number)[] = [];
    const result = await runPool(eligible, BULK_CONCURRENCY, async (row) => {
      await api.patch(itemPath(row), body);
      written.push(row.id);
    });
    return finishBulk(result, written, kept, false);
  };

  /** Annonce, résume et recharge après une action groupée */
  const finishBulk = async (
    result: BulkResult,
    written: (string | number)[],
    kept: number,
    deleted: boolean
  ): Promise<BulkResult> => {
    if (written.length > 0) announce(written, deleted);
    result.failed += kept;
    notify(result.failed > 0 ? 'warning' : 'success', t('admin_app.screen.bulk_result', { ...result }));
    collection.selected.value = [];
    await collection.load();
    return result;
  };

  const runBulk = async (id: string, keys: (string | number)[]): Promise<BulkResult | null> => {
    const action = descriptor.bulkActions?.find((candidate) =>
      candidate.type === 'patch' ? candidate.id === id : candidate.type === id
    );
    if (!action) return null;
    const rows = keys.map((key) => collection.known.get(key)).filter((row): row is T => row !== undefined);
    if (action.type === 'patch') return runPatch(action, rows, keys.length);
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
    return finishBulk(result, written, kept, isDelete);
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
    get createHref(): string {
      return descriptor.create ? router.resolve({ name: descriptor.create.route, query: listQuery() }).href : '';
    },
    term,
    listQuery,
    statusOptions: [
      { value: '', label: t('admin_app.screen.filter_status_all') },
      { value: 'enabled', label: t('admin_app.screen.filter_status_enabled') },
      { value: 'disabled', label: t('admin_app.screen.filter_status_disabled') },
    ],
    authorOptions: [
      { value: '', label: t('admin_app.screen.filter_author_all') },
      {
        value: 'me',
        label: descriptor.messages.authorMe ? t(descriptor.messages.authorMe) : t('admin_app.screen.filter_author_me'),
      },
    ],
    selectFilters: (descriptor.filters ?? [])
      .filter((filter) => typeof filter !== 'string' && filter.type === 'select')
      .map((filter) => ({
        param: filter.param,
        label: t(filter.label),
        options: [
          { value: '', label: t(filter.allLabel) },
          ...filter.options.map((option) => ({ value: option.value, label: t(option.label) })),
        ],
      })),
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
        if (edit) await router.push({ name: edit.route, params: { id: String(row.id) }, query: listQuery() });
      } else if (id === 'disable' || id === 'enable') {
        await setDisabled(row, id === 'disable');
      } else if (id === 'delete') {
        await remove(row);
      }
    },
    runBulk,
    bulkDialog,
    answerBulkDialog(payload: Record<string, unknown> | null): void {
      const resolve = answerDialog;
      answerDialog = null;
      bulkDialog.value = null;
      resolve?.(payload);
    },
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
    filter: Object.fromEntries(
      [...new Set(['status', 'author', ...filterParams(descriptor as CollectionScreenDescriptor<never>)])].map(
        (param) => [param, filterRef(param)]
      )
    ),
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
  const route = useRoute();
  const router = useRouter();
  const params = filterParams(descriptor as CollectionScreenDescriptor<never>);
  const perPage = bootstrap.options.itemsPerPage;
  const state = listStateFromQuery(route.query, params, perPage);
  const collection = createAdminCollection<T>(api, descriptor.resource, {
    ...state,
    withSql: can.canOperation(SQL_QUERY_OPERATION),
  });
  const term = ref(state.search);
  const listName = route.meta.screenGroup;
  if (listName !== undefined) {
    onScopeDispose(
      bindListQuery(collection as AdminCollection<unknown>, term, params, route, router, listName, perPage)
    );
  }
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
    router,
    collection,
    locale: bootstrap.locale,
    term,
    listQuery: () => route.query,
  });
}

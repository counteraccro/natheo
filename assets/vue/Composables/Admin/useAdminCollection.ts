/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Collection d'administration affichée dans une grille : page, nombre par page, tri, recherche, filtres, sélection,
 * chargement et erreur ; une réponse arrivée après une requête plus récente est ignorée
 */
import { reactive, ref, shallowRef, watch, type Ref, type ShallowRef } from 'vue';
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { ApiClient, ApiQuery } from '@/vue/Composables/Global/Api/ApiClient';
import type { DataTableSort } from '@/ts/Admin/Ui/Data/UiDataTable.type';

export type CollectionFilterValue = string | number | boolean | null | undefined;

export interface AdminCollectionOptions {
  /** Option utilisateur OU_NB_ELEMENT par défaut */
  itemsPerPage?: number;
  sort?: DataTableSort | null;
  /** Recherche en base et page de départ (état de la liste lu dans l'adresse) */
  search?: string;
  page?: number;
  /** Filtres de l'opération (status…), envoyés tels quels */
  filters?: Record<string, CollectionFilterValue>;
  /** Demande la requête SQL de la page (super administrateur) */
  withSql?: boolean;
}

/** Nombre d'éléments lus par requête et plafond de « tout sélectionner » */
export const SELECT_ALL_PAGE_SIZE = 100;
export const SELECT_ALL_LIMIT = 1000;

export interface AdminCollection<T> {
  items: ShallowRef<T[]>;
  total: Ref<number>;
  lastPage: Ref<number>;
  page: Ref<number>;
  itemsPerPage: Ref<number>;
  sort: Ref<DataTableSort | null>;
  search: Ref<string>;
  filters: Record<string, CollectionFilterValue>;
  /** Clés des lignes sélectionnées, gardées d'une page à l'autre */
  selected: Ref<(string | number)[]>;
  /** Éléments lus, par clé, pour retrouver une ligne sélectionnée sur une autre page */
  known: Map<string | number, T>;
  /** Requête SQL de la page, null sans withSql */
  sql: Ref<string | null>;
  loading: Ref<boolean>;
  error: ShallowRef<ApiError | null>;
  /** Recharge la page courante (après une suppression…) */
  load(): Promise<void>;
  /**
   * Sélectionne toute la collection avec les mêmes filtres, lue page par page
   * @return true si le plafond SELECT_ALL_LIMIT a coupé la sélection
   */
  selectAll(): Promise<boolean>;
}

/**
 * Crée la collection ; tout changement de page, de tri, de recherche ou de filtre la recharge
 * @param api client de l'API
 * @param resource chemin de la collection (/admin/tags)
 * @param options
 */
export function createAdminCollection<T>(
  api: ApiClient,
  resource: string,
  options: AdminCollectionOptions = {}
): AdminCollection<T> {
  const page = ref(options.page ?? 1);
  const itemsPerPage = ref(options.itemsPerPage ?? 20);
  const sort = ref<DataTableSort | null>(options.sort ?? null);
  const search = ref(options.search ?? '');
  const filters = reactive<Record<string, CollectionFilterValue>>({ ...options.filters });
  const items = shallowRef<T[]>([]);
  const total = ref(0);
  const lastPage = ref(1);
  const loading = ref(false);
  const error = shallowRef<ApiError | null>(null);
  const sql = ref<string | null>(null);
  const selected = ref<(string | number)[]>([]);
  const known = new Map<string | number, T>();
  let lastRequest = 0;

  const remember = (members: T[]): void => {
    members.forEach((member) => known.set((member as { id: string | number }).id, member));
  };

  const query = (): ApiQuery => ({
    page: page.value,
    itemsPerPage: itemsPerPage.value,
    search: search.value.trim(),
    ...(sort.value ? { [`order[${sort.value.key}]`]: sort.value.direction } : {}),
    ...filters,
    ...(options.withSql ? { sql: true } : {}),
  });

  const load = async (): Promise<void> => {
    const request = ++lastRequest;
    loading.value = true;
    error.value = null;
    try {
      const result = await api.getCollection<T>(resource, query());
      if (request !== lastRequest) return;
      // Page vidée par une suppression : retour à la dernière page qui existe
      if (result.members.length === 0 && page.value > 1 && result.total > 0) {
        page.value = result.lastPage;
        return;
      }
      items.value = result.members;
      total.value = result.total;
      lastPage.value = result.lastPage;
      sql.value = result.sql ?? null;
      remember(result.members);
    } catch (caught) {
      if (request !== lastRequest) return;
      error.value = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
      notify('danger', error.value.title, error.value.detail ?? undefined);
    } finally {
      if (request === lastRequest) loading.value = false;
    }
  };

  watch([page, itemsPerPage], () => void load());
  watch([sort, search, () => ({ ...filters })], () => {
    // Le changement de page relance le chargement
    if (page.value !== 1) page.value = 1;
    else void load();
  });

  const selectAll = async (): Promise<boolean> => {
    const keys: (string | number)[] = [];
    let current = 1;
    let last = 1;
    do {
      const result = await api.getCollection<T>(resource, {
        ...query(),
        sql: undefined,
        page: current,
        itemsPerPage: SELECT_ALL_PAGE_SIZE,
      });
      remember(result.members);
      keys.push(...result.members.map((member) => (member as { id: string | number }).id));
      last = result.lastPage;
      current++;
    } while (current <= last && keys.length < SELECT_ALL_LIMIT);
    selected.value = keys.slice(0, SELECT_ALL_LIMIT);
    return keys.length > SELECT_ALL_LIMIT || current <= last;
  };

  return {
    items,
    total,
    lastPage,
    page,
    itemsPerPage,
    sort,
    search,
    filters,
    selected,
    known,
    sql,
    loading,
    error,
    load,
    selectAll,
  };
}

/**
 * Collection d'un écran, avec le nombre par page de l'utilisateur
 * @param resource chemin de la collection (/admin/tags)
 * @param options
 */
export function useAdminCollection<T>(resource: string, options: AdminCollectionOptions = {}): AdminCollection<T> {
  const { api, bootstrap } = useAdminContext();
  return createAdminCollection<T>(api, resource, { itemsPerPage: bootstrap.options.itemsPerPage, ...options });
}

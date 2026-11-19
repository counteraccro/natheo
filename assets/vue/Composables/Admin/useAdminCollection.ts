/**
 * @author Gourdon Aymeric
 * @version 1.0
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
  /** Filtres de l'opération (status…), envoyés tels quels */
  filters?: Record<string, CollectionFilterValue>;
}

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
  loading: Ref<boolean>;
  error: ShallowRef<ApiError | null>;
  /** Recharge la page courante (après une suppression…) */
  load(): Promise<void>;
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
  const page = ref(1);
  const itemsPerPage = ref(options.itemsPerPage ?? 20);
  const sort = ref<DataTableSort | null>(options.sort ?? null);
  const search = ref('');
  const filters = reactive<Record<string, CollectionFilterValue>>({ ...options.filters });
  const items = shallowRef<T[]>([]);
  const total = ref(0);
  const lastPage = ref(1);
  const loading = ref(false);
  const error = shallowRef<ApiError | null>(null);
  let lastRequest = 0;

  const query = (): ApiQuery => ({
    page: page.value,
    itemsPerPage: itemsPerPage.value,
    search: search.value.trim(),
    ...(sort.value ? { [`order[${sort.value.key}]`]: sort.value.direction } : {}),
    ...filters,
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

  return {
    items,
    total,
    lastPage,
    page,
    itemsPerPage,
    sort,
    search,
    filters,
    selected: ref([]),
    loading,
    error,
    load,
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

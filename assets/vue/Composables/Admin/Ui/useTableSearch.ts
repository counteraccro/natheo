/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Recherche en deux temps d'une grille : filtre de la page affichée, puis requête en base quand elle ne contient rien
 */
import { computed, getCurrentInstance, onBeforeUnmount, ref, watch, type ComputedRef, type Ref } from 'vue';
import { filterRows } from '@/vue/Composables/Admin/Ui/tableSearch';
import type { DataTableColumn, DataTableRow, DataTableSearchMode } from '@/ts/Admin/Ui/Data/UiDataTable.type';

/** Saisie minimale et délai avant la recherche en base quand la page affichée ne contient rien */
const REMOTE_MIN_LENGTH = 2;
const REMOTE_DELAY = 450;

export interface TableSearchOptions<T extends DataTableRow> {
  rows: () => T[];
  /** Colonnes affichées, celles où le terme est cherché */
  columns: () => DataTableColumn[];
  loading: () => boolean | undefined;
  /** Recherche en base, terme vide pour revenir à la collection entière */
  search: (term: string) => void;
  /** Retour à la première page avant une recherche en base */
  resetPage: () => void;
}

export interface TableSearch<T extends DataTableRow> {
  term: Ref<string>;
  mode: Ref<DataTableSearchMode>;
  /** Lignes affichées : filtrées en recherche locale */
  displayed: ComputedRef<T[]>;
  /** Terme à surligner dans les cellules */
  highlightTerm: ComputedRef<string>;
  onInput(): void;
  clear(): void;
  /** Lance tout de suite la recherche en base du terme saisi */
  searchRemote(): void;
}

/**
 * Crée l'état de la recherche
 * @param options
 */
export function useTableSearch<T extends DataTableRow>(options: TableSearchOptions<T>): TableSearch<T> {
  const term = ref('');
  const mode = ref<DataTableSearchMode>('idle');
  let remoteTerm: string | null = null;
  let timer: ReturnType<typeof setTimeout> | undefined;

  const searchRemote = (value: string): void => {
    clearTimeout(timer);
    if (!value) return;
    mode.value = 'pending';
    remoteTerm = value;
    options.resetPage();
    options.search(value);
  };

  const onInput = (): void => {
    clearTimeout(timer);
    const value = term.value.trim();
    if (!value) {
      mode.value = 'idle';
      if (remoteTerm !== null) {
        remoteTerm = null;
        options.resetPage();
        options.search('');
      }
      return;
    }
    mode.value = 'local';
    if (value.length >= REMOTE_MIN_LENGTH && filterRows(options.rows(), options.columns(), value).length === 0) {
      timer = setTimeout(() => searchRemote(value), REMOTE_DELAY);
    }
  };

  // La réponse arrive avec de nouvelles lignes ou la fin du chargement, y compris en cas d'échec
  watch(
    () => [options.rows(), options.rows().length, options.loading()] as const,
    ([, , loading]) => {
      if (mode.value === 'pending' && !loading) mode.value = 'remote';
    }
  );
  if (getCurrentInstance()) {
    onBeforeUnmount(() => clearTimeout(timer));
  }

  return {
    term,
    mode,
    displayed: computed(() =>
      mode.value === 'local' ? filterRows(options.rows(), options.columns(), term.value) : options.rows()
    ),
    highlightTerm: computed(() => (mode.value === 'local' || mode.value === 'remote' ? term.value.trim() : '')),
    onInput,
    clear(): void {
      term.value = '';
      onInput();
    },
    searchRemote(): void {
      searchRemote(term.value.trim());
    },
  };
}

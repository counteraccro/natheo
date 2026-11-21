import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick, ref } from 'vue';
import { useTableSearch } from '@/vue/Composables/Admin/Ui/useTableSearch';

const columns = [{ key: 'label', label: 'Libellé' }];

function setup() {
  const rows = ref([
    { id: 1, label: 'Été' },
    { id: 2, label: 'Hiver' },
  ]);
  const loading = ref(false);
  const search = vi.fn();
  const resetPage = vi.fn();
  const state = useTableSearch({
    rows: () => rows.value,
    columns: () => columns,
    loading: () => loading.value,
    search,
    resetPage,
  });
  return { rows, loading, search, resetPage, state };
}

beforeEach(() => vi.useFakeTimers());
afterEach(() => vi.useRealTimers());

describe('useTableSearch', () => {
  it('filtre la page affichée sans accents ni casse', () => {
    const { state, search } = setup();
    state.term.value = 'ete';
    state.onInput();
    expect(state.mode.value).toBe('local');
    expect(state.displayed.value.map((row) => row.id)).toEqual([1]);
    expect(state.highlightTerm.value).toBe('ete');
    vi.runAllTimers();
    expect(search).not.toHaveBeenCalled();
  });

  it('cherche en base après un délai quand la page ne contient rien, puis revient à la collection', async () => {
    const { state, search, resetPage, rows } = setup();
    state.term.value = 'printemps';
    state.onInput();
    vi.advanceTimersByTime(450);
    expect(state.mode.value).toBe('pending');
    expect(resetPage).toHaveBeenCalledTimes(1);
    expect(search).toHaveBeenCalledWith('printemps');

    rows.value = [{ id: 3, label: 'Printemps' }];
    await nextTick();
    expect(state.mode.value).toBe('remote');

    state.clear();
    expect(state.mode.value).toBe('idle');
    expect(search).toHaveBeenLastCalledWith('');
  });

  it('lance la recherche en base tout de suite sur demande', () => {
    const { state, search } = setup();
    state.term.value = ' Été ';
    state.searchRemote();
    expect(search).toHaveBeenCalledWith('Été');
  });
});

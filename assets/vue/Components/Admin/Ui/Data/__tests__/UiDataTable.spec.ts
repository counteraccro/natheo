import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import UiDataTable from '@/vue/Components/Admin/Ui/Data/UiDataTable.vue';
import UiPagination from '@/vue/Components/Admin/Ui/Data/UiPagination.vue';
import type { DataTableAction } from '@/ts/Admin/Ui/Data/UiDataTable.type';
import { withLabels } from '../../__tests__/support';

afterEach(() => {
  document.body.innerHTML = '';
});

const columns = [
  { key: 'label', label: 'Libellé', sortable: true },
  { key: 'pages', label: 'Utilisation', align: 'end' as const },
  { key: 'id', label: 'ID', hidden: true },
];
const rows = [
  { id: 1, label: 'Écologie', pages: 3 },
  { id: 2, label: 'Vue', pages: 12 },
];
const actions: DataTableAction[] = [
  { id: 'edit', label: 'Modifier', icon: 'edit' },
  { id: 'disable', label: 'Désactiver', icon: 'ban' },
  { id: 'delete', label: 'Supprimer', icon: 'trash', danger: true },
];

const mountTable = (props: Record<string, unknown> = {}) =>
  mount(UiDataTable, withLabels({ props: { label: 'Tags', columns, rows, totalItems: 2, ...props }, attachTo: document.body }));

const bodyRows = (wrapper: ReturnType<typeof mountTable>) => wrapper.findAll('tbody tr');

describe('UiDataTable', () => {
  it('affiche les lignes en texte et masque la colonne ID par défaut', () => {
    const wrapper = mountTable({ rows: [{ id: 1, label: '<b>Vue</b>', pages: 1 }] });
    expect(wrapper.findAll('th').map((th) => th.text())).toEqual(['Libellé', 'Utilisation']);
    expect(wrapper.get('tbody td').text()).toBe('<b>Vue</b>');
    expect(wrapper.find('tbody b').exists()).toBe(false);
  });

  it('affiche une colonne masquée depuis le menu Options', async () => {
    const wrapper = mountTable();
    await wrapper.get('button[aria-haspopup="menu"]').trigger('click');
    await nextTick();
    await nextTick();
    const item = document.querySelector<HTMLElement>('[role="menuitemcheckbox"]')!;
    expect(item.textContent).toContain('ID');
    expect(item.getAttribute('aria-checked')).toBe('false');
    item.click();
    await nextTick();
    expect(wrapper.findAll('th').map((th) => th.text())).toContain('ID');
  });

  it('trie par en-tête et annonce le tri', async () => {
    const wrapper = mountTable({ sort: { key: 'label', direction: 'asc' } });
    const th = wrapper.get('th');
    expect(th.attributes('aria-sort')).toBe('ascending');
    expect(wrapper.findAll('th')[1].attributes('aria-sort')).toBeUndefined();
    await th.get('button').trigger('click');
    expect(wrapper.emitted('update:sort')?.[0]).toEqual([{ key: 'label', direction: 'desc' }]);
  });

  it('affiche les actions fournies par ligne : deux boutons puis un menu', async () => {
    const wrapper = mountTable({ actionsFor: (row: { id: number }) => (row.id === 1 ? actions : actions.slice(0, 1)) });
    const [first, second] = bodyRows(wrapper);
    expect(first.findAll('[aria-label="Modifier"], [aria-label="Désactiver"]')).toHaveLength(2);
    expect(first.findAll('[aria-label="data_table.more_actions"]')).toHaveLength(2);
    expect(second.findAll('[aria-label="Désactiver"]')).toHaveLength(0);
    await first.get('[aria-label="Modifier"]').trigger('click');
    expect(wrapper.emitted('action')?.[0]).toEqual([{ id: 'edit', row: rows[0] }]);
  });

  it('montre un squelette pendant le chargement et un état vide sans ligne', async () => {
    const wrapper = mountTable({ loading: true });
    expect(wrapper.get('[aria-busy="true"]').exists()).toBe(true);
    expect(wrapper.findAll('.ui-skeleton').length).toBeGreaterThan(0);
    await wrapper.setProps({ loading: false, rows: [], totalItems: 0 });
    expect(wrapper.text()).toContain('data_table.empty_title');
  });

  describe('recherche en deux temps', () => {
    beforeEach(() => vi.useFakeTimers());
    afterEach(() => vi.useRealTimers());

    it('filtre la page affichée pendant la frappe et surligne', async () => {
      const wrapper = mountTable();
      await wrapper.get('input[type="search"]').setValue('eco');
      expect(bodyRows(wrapper)).toHaveLength(1);
      expect(wrapper.get('mark').text()).toBe('Éco');
      expect(wrapper.text()).toContain('data_table.search_local(1,2)');
      vi.advanceTimersByTime(1000);
      expect(wrapper.emitted('search')).toBeUndefined();
    });

    it('cherche en base quand la page ne contient rien, puis revient au tableau', async () => {
      const wrapper = mountTable();
      await wrapper.get('input[type="search"]').setValue('symfony');
      vi.advanceTimersByTime(449);
      expect(wrapper.emitted('search')).toBeUndefined();
      vi.advanceTimersByTime(1);
      await nextTick();
      expect(wrapper.emitted('search')?.[0]).toEqual(['symfony']);
      expect(wrapper.text()).toContain('data_table.search_pending');
      await wrapper.setProps({ rows: [{ id: 9, label: 'Symfony', pages: 4 }], totalItems: 1 });
      expect(wrapper.text()).toContain('data_table.search_remote(1)');
      await wrapper.get('button.underline').trigger('click');
      expect(wrapper.emitted('search')?.[1]).toEqual(['']);
    });

    it('cherche en base tout de suite sur Entrée', async () => {
      const wrapper = mountTable();
      const input = wrapper.get('input[type="search"]');
      await input.setValue('vue');
      await input.trigger('keydown', { key: 'Enter' });
      expect(wrapper.emitted('search')?.[0]).toEqual(['vue']);
    });
  });
});

describe('UiPagination', () => {
  it('annonce la plage et change de page', async () => {
    const wrapper = mount(UiPagination, withLabels({ props: { totalItems: 48, page: 1, itemsPerPage: 20 } }));
    expect(wrapper.get('[aria-live]').text()).toBe('pagination.range(1,20,48)');
    expect(wrapper.get('[aria-label="pagination.previous"]').attributes('disabled')).toBeDefined();
    expect(wrapper.get('[aria-current="page"]').text()).toBe('1');
    await wrapper.get('[aria-label="pagination.next"]').trigger('click');
    expect(wrapper.emitted('update:page')?.[0]).toEqual([2]);
  });

  it('revient à la première page quand le nombre par page change', async () => {
    const wrapper = mount(UiPagination, withLabels({ props: { totalItems: 48, page: 3, itemsPerPage: 20 } }));
    await wrapper.get('select').setValue('50');
    expect(wrapper.emitted('update:itemsPerPage')?.[0]).toEqual([50]);
    expect(wrapper.emitted('update:page')?.[0]).toEqual([1]);
  });
});

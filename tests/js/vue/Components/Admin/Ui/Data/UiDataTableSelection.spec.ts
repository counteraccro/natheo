import { afterEach, describe, expect, it } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import UiDataTable from '@/vue/Components/Admin/Ui/Data/UiDataTable.vue';
import { withLabels } from '@tests/vue/Components/Admin/Ui/support';

afterEach(() => {
  document.body.innerHTML = '';
});

const columns = [{ key: 'label', label: 'Libellé' }];
const page1 = [
  { id: 1, label: 'Écologie' },
  { id: 2, label: 'Vue' },
];
const page2 = [{ id: 3, label: 'Symfony' }];
const bulkActions = [
  { id: 'delete', label: 'Supprimer', icon: 'trash' as const, danger: true },
  { id: 'disable', label: 'Désactiver', icon: 'ban' as const },
];

const mountTable = (selected: (string | number)[] = []) => {
  const wrapper = mount(
    UiDataTable,
    withLabels({
      props: {
        label: 'Tags',
        columns,
        rows: page1,
        totalItems: 3,
        selectable: true,
        bulkActions,
        selected,
        'onUpdate:selected': (value: (string | number)[]) => wrapper.setProps({ selected: value }),
      },
      attachTo: document.body,
    })
  );
  return wrapper;
};

const headerBox = (wrapper: ReturnType<typeof mountTable>) =>
  wrapper.get('th input[type="checkbox"]').element as HTMLInputElement;

describe('UiDataTable : sélection multiple', () => {
  it('nomme chaque case et passe la case d’en-tête à l’état intermédiaire', async () => {
    const wrapper = mountTable();
    const boxes = wrapper.findAll('tbody input[type="checkbox"]');
    expect(boxes[0].attributes('aria-label')).toBe('data_table.select_row(Écologie)');
    expect(wrapper.find('[role="region"]').exists()).toBe(false);
    await boxes[0].setValue(true);
    await nextTick();
    expect(headerBox(wrapper).indeterminate).toBe(true);
    expect(wrapper.get('[role="region"]').text()).toContain('data_table.selected(1)');
  });

  it('sélectionne la page, propose toute la collection et garde la sélection au changement de page', async () => {
    const wrapper = mountTable();
    await wrapper.get('th input[type="checkbox"]').setValue(true);
    expect(wrapper.props('selected')).toEqual([1, 2]);
    const region = wrapper.get('[role="region"]');
    await region
      .findAll('button')
      .find((b) => b.text() === 'data_table.select_all(3)')!
      .trigger('click');
    expect(wrapper.emitted('selectAll')).toHaveLength(1);

    await wrapper.setProps({ rows: page2 });
    expect(headerBox(wrapper).checked).toBe(false);
    await wrapper.get('tbody input[type="checkbox"]').setValue(true);
    expect(wrapper.props('selected')).toEqual([1, 2, 3]);
  });

  it('émet les actions groupées, la destructive en dernier', async () => {
    const wrapper = mountTable([1, 2]);
    const buttons = wrapper.get('[role="region"]').findAll('.ml-auto button');
    expect(buttons.map((b) => b.text())).toEqual(['Désactiver', 'Supprimer']);
    await buttons[1].trigger('click');
    expect(wrapper.emitted('bulkAction')?.[0]).toEqual([{ id: 'delete', keys: [1, 2] }]);
  });

  it('vide la sélection et rend le focus à la case d’en-tête', async () => {
    const wrapper = mountTable([1]);
    await wrapper
      .get('[role="region"]')
      .findAll('button')
      .find((b) => b.text() === 'data_table.clear_selection')!
      .trigger('click');
    await nextTick();
    await nextTick();
    expect(wrapper.props('selected')).toEqual([]);
    expect(document.activeElement).toBe(headerBox(wrapper));
  });
});

import { describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import UiSqlBlock from '@/vue/Components/Admin/Ui/Data/UiSqlBlock.vue';
import { withLabels } from '@tests/vue/Components/Admin/Ui/support';

const copyToClipboard = vi.fn(() => Promise.resolve());
vi.mock('@/vue/Composables/Global/copyToClipboard', () => ({ copyToClipboard: () => copyToClipboard() }));

describe('UiSqlBlock', () => {
  it('confirme la copie de la requête', async () => {
    const wrapper = mount(UiSqlBlock, withLabels({ props: { sql: 'SELECT 1' } }));
    const button = wrapper.findAll('button')[1];
    expect(button.text()).toBe('data_table.sql_copy');
    await button.trigger('click');
    await flushPromises();
    expect(copyToClipboard).toHaveBeenCalled();
    expect(button.text()).toBe('data_table.sql_copied');
    expect(wrapper.get('[role="status"]').text()).toBe('data_table.sql_copied');
  });

  it("n'annonce rien quand la copie échoue", async () => {
    copyToClipboard.mockRejectedValueOnce(new Error('refus'));
    const wrapper = mount(UiSqlBlock, withLabels({ props: { sql: 'SELECT 1' } }));
    await wrapper.findAll('button')[1].trigger('click');
    await flushPromises();
    expect(wrapper.get('[role="status"]').text()).toBe('');
  });

  it('désactive Enregistrer quand la requête est déjà enregistrée', async () => {
    const wrapper = mount(UiSqlBlock, withLabels({ props: { sql: 'SELECT 1', saved: true } }));
    const button = wrapper.findAll('button')[0];
    expect(button.text()).toBe('data_table.sql_saved');
    expect(button.attributes('disabled')).toBeDefined();
    await button.trigger('click');
    expect(wrapper.emitted('save')).toBeUndefined();
  });
});

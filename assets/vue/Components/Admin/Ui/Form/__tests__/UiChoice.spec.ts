import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import UiCheckbox from '@/vue/Components/Admin/Ui/Form/UiCheckbox.vue';
import UiSwitch from '@/vue/Components/Admin/Ui/Form/UiSwitch.vue';
import UiSelect from '@/vue/Components/Admin/Ui/Form/UiSelect.vue';
import UiColorInput from '@/vue/Components/Admin/Ui/Form/UiColorInput.vue';
import { withLabels } from '../../__tests__/support';

describe('UiCheckbox', () => {
  it("passe à l'état intermédiaire", async () => {
    const wrapper = mount(UiCheckbox, { props: { indeterminate: true }, attrs: { 'aria-label': 'Tout' } });
    expect((wrapper.get('input').element as HTMLInputElement).indeterminate).toBe(true);
    await wrapper.setProps({ indeterminate: false });
    expect((wrapper.get('input').element as HTMLInputElement).indeterminate).toBe(false);
  });

  it('transmet son état', async () => {
    const wrapper = mount(UiCheckbox, { props: { label: 'Actif' } });
    await wrapper.get('input').setValue(true);
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([true]);
  });
});

describe('UiSwitch', () => {
  it('bascule au clic et expose son état', async () => {
    const wrapper = mount(UiSwitch, {
      props: { label: 'Actif', modelValue: false, 'onUpdate:modelValue': (v: boolean) => wrapper.setProps({ modelValue: v }) },
    });
    const button = wrapper.get('[role="switch"]');
    expect(button.attributes('aria-checked')).toBe('false');
    expect(button.attributes('aria-labelledby')).toBe(wrapper.get('span').attributes('id'));
    await button.trigger('click');
    expect(button.attributes('aria-checked')).toBe('true');
  });
});

describe('UiSelect', () => {
  it('liste ses options et transmet la valeur choisie', async () => {
    const wrapper = mount(UiSelect, {
      props: { options: [{ value: 'fr', label: 'Français' }, { value: 'en', label: 'English' }] },
    });
    expect(wrapper.findAll('option')).toHaveLength(2);
    await wrapper.get('select').setValue('en');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['en']);
  });
});

describe('UiColorInput', () => {
  it('ne transmet que les codes hexadécimaux valides', async () => {
    const wrapper = mount(UiColorInput, withLabels({ props: { modelValue: '#5b3fd1' } }));
    const text = wrapper.get('input[type="text"]');
    await text.setValue('#12');
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    expect(text.attributes('aria-invalid')).toBe('true');
    await text.setValue('#A1B2C3');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['#a1b2c3']);
    expect(text.attributes('aria-invalid')).toBeUndefined();
  });
});

import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import UiColorInput from '@/vue/Components/Admin/Ui/Form/UiColorInput.vue';
import { withLabels } from '@/vue/Components/Admin/Ui/__tests__/support';

describe('UiColorInput', () => {
  it('ne propose aucune couleur par défaut', () => {
    const wrapper = mount(UiColorInput, withLabels({ props: { modelValue: '#112233' } }));
    expect(wrapper.find('[role="group"]').exists()).toBe(false);
  });

  it('propose des couleurs au hasard, en choisit une et en tire d’autres', async () => {
    const wrapper = mount(UiColorInput, withLabels({ props: { modelValue: '#112233', suggestions: 5 } }));
    const swatches = () => wrapper.findAll('[role="group"] button[aria-pressed]');
    expect(swatches()).toHaveLength(5);
    const first = swatches()[0].attributes('title')!;
    expect(first).toMatch(/^#[0-9a-f]{6}$/);

    await swatches()[0].trigger('click');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([first]);

    const before = swatches().map((swatch) => swatch.attributes('title'));
    await wrapper.find('[role="group"] button:not([aria-pressed])').trigger('click');
    expect(swatches().map((swatch) => swatch.attributes('title'))).not.toEqual(before);
  });

  it('relie l’aide placée à gauche des couleurs proposées au code hexadécimal', () => {
    const wrapper = mount(UiColorInput, withLabels({ props: { suggestions: 2, hint: 'Format #rrggbb' } }));
    const hint = wrapper.get('p');
    expect(hint.text()).toBe('Format #rrggbb');
    expect(wrapper.get('input[type="text"]').attributes('aria-describedby')).toBe(hint.attributes('id'));
  });
});

import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import UiButton from '@/vue/Components/Admin/Ui/Form/UiButton.vue';
import { withLabels } from '@tests/vue/Components/Admin/Ui/support';

describe('UiButton', () => {
  it('est un bouton de type button par défaut', () => {
    const wrapper = mount(UiButton, withLabels({ slots: { default: 'Enregistrer' } }));
    expect(wrapper.get('button').attributes('type')).toBe('button');
    expect(wrapper.text()).toBe('Enregistrer');
  });

  it('devient un lien avec href', () => {
    const wrapper = mount(UiButton, withLabels({ props: { href: '/admin' }, slots: { default: 'Retour' } }));
    expect(wrapper.get('a').attributes('href')).toBe('/admin');
  });

  it('se désactive pendant le chargement', () => {
    const wrapper = mount(UiButton, withLabels({ props: { loading: true }, slots: { default: 'Enregistrer' } }));
    const button = wrapper.get('button');
    expect(button.attributes('aria-busy')).toBe('true');
    expect(button.attributes('disabled')).toBeDefined();
    expect(wrapper.text()).toContain('button.loading');
  });

  it('signale un bouton icône sans aria-label', () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});
    mount(UiButton, withLabels({ props: { icon: 'trash' } }));
    expect(warn).toHaveBeenCalledWith(expect.stringContaining('aria-label'));
  });

  it('accepte un bouton icône avec aria-label', () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});
    mount(UiButton, withLabels({ props: { icon: 'trash' }, attrs: { 'aria-label': 'Supprimer' } }));
    expect(warn).not.toHaveBeenCalled();
  });
});

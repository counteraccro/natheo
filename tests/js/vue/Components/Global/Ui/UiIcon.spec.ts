import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { ICONS } from '@/vue/Composables/Global/icons';

describe('UiIcon', () => {
  it('dessine une icône du registre au trait, décorative par défaut', () => {
    const wrapper = mount(UiIcon, { props: { name: 'trash' } });
    const svg = wrapper.get('svg');
    expect(svg.attributes('aria-hidden')).toBe('true');
    expect(svg.attributes('role')).toBeUndefined();
    expect(svg.attributes('fill')).toBe('none');
    expect(wrapper.get('path').attributes('d')).toBe(ICONS.trash.paths[0]);
    expect(wrapper.get('path').attributes('stroke')).toBe('currentColor');
  });

  it('remplit une icône pleine', () => {
    const wrapper = mount(UiIcon, { props: { name: 'linkedin' } });
    expect(wrapper.get('svg').attributes('fill')).toBe('currentColor');
    expect(wrapper.findAll('path')).toHaveLength(2);
  });

  it("dessine le tracé reçu de l'API", () => {
    const wrapper = mount(UiIcon, { props: { path: 'M5 12h14' } });
    expect(wrapper.get('path').attributes('d')).toBe('M5 12h14');
  });

  it('devient signifiante avec un libellé', () => {
    const wrapper = mount(UiIcon, { props: { name: 'info', label: 'Information' } });
    const svg = wrapper.get('svg');
    expect(svg.attributes('role')).toBe('img');
    expect(svg.attributes('aria-label')).toBe('Information');
    expect(svg.attributes('aria-hidden')).toBeUndefined();
  });

  it("n'utilise aucune variable du back-office", () => {
    expect(JSON.stringify(ICONS)).not.toContain('var(');
  });
});

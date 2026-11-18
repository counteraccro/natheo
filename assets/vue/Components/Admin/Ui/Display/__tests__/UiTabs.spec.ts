import { afterEach, describe, expect, it } from 'vitest';
import { h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import UiTabs from '@/vue/Components/Admin/Ui/Display/UiTabs.vue';
import UiBadge from '@/vue/Components/Admin/Ui/Display/UiBadge.vue';

afterEach(() => {
  document.body.innerHTML = '';
});

const tabs = [
  { id: 'fr', label: 'Français' },
  { id: 'en', label: 'English' },
  { id: 'es', label: 'Español' },
];

const mountTabs = () =>
  mount(UiTabs, {
    props: { tabs, label: 'Langues', modelValue: 'fr', 'onUpdate:modelValue': (v: string) => wrapper.setProps({ modelValue: v }) },
    slots: { fr: () => h('p', 'Panneau FR'), en: () => h('p', 'Panneau EN'), es: () => h('p', 'Panneau ES') },
    attachTo: document.body,
  });
let wrapper: ReturnType<typeof mountTabs>;

describe('UiTabs', () => {
  it('relie les onglets à leurs panneaux et ne monte que le panneau ouvert', () => {
    wrapper = mountTabs();
    const [fr] = wrapper.findAll('[role="tab"]');
    expect(fr.attributes('aria-selected')).toBe('true');
    expect(fr.attributes('tabindex')).toBe('0');
    const panel = wrapper.get('[role="tabpanel"]');
    expect(panel.attributes('aria-labelledby')).toBe(fr.attributes('id'));
    expect(wrapper.findAll('[role="tabpanel"]')).toHaveLength(1);
  });

  it('déplace le focus avec les flèches sans activer, puis active au clic', async () => {
    wrapper = mountTabs();
    const tab = (i: number) => wrapper.findAll('[role="tab"]')[i];
    (tab(0).element as HTMLElement).focus();
    await wrapper.get('[role="tablist"]').trigger('keydown', { key: 'ArrowRight' });
    await nextTick();
    expect(document.activeElement).toBe(tab(1).element);
    expect(tab(1).attributes('tabindex')).toBe('0');
    expect(tab(0).attributes('aria-selected')).toBe('true');
    await wrapper.get('[role="tablist"]').trigger('keydown', { key: 'ArrowLeft' });
    await wrapper.get('[role="tablist"]').trigger('keydown', { key: 'ArrowLeft' });
    await nextTick();
    expect(document.activeElement).toBe(tab(2).element);
    await tab(2).trigger('click');
    await nextTick();
    expect(tab(2).attributes('aria-selected')).toBe('true');
    expect(wrapper.text()).toContain('Panneau ES');
  });
});

describe('UiBadge', () => {
  it('affiche un état avec son point ou un compteur', () => {
    expect(mount(UiBadge, { props: { tone: 'success' }, slots: { default: 'Actif' } }).find('[aria-hidden]').exists()).toBe(true);
    expect(mount(UiBadge, { props: { count: 12 } }).text()).toBe('12');
  });
});

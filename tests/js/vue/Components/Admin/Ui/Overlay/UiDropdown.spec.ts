import { afterEach, describe, expect, it } from 'vitest';
import { h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import UiDropdown from '@/vue/Components/Admin/Ui/Overlay/UiDropdown.vue';
import UiDropdownItem from '@/vue/Components/Admin/Ui/Overlay/UiDropdownItem.vue';
import UiTooltip from '@/vue/Components/Admin/Ui/Overlay/UiTooltip.vue';
import { withLabels } from '@tests/vue/Components/Admin/Ui/support';

afterEach(() => {
  document.body.innerHTML = '';
});

const flush = async () => {
  await nextTick();
  await nextTick();
};

const mountMenu = (onSelect = () => {}) =>
  mount(
    UiDropdown,
    withLabels({
      props: { label: "Plus d'actions", icon: 'dots', iconOnly: true },
      slots: {
        default: () => [
          h(UiDropdownItem, { icon: 'edit', onSelect }, () => 'Modifier'),
          h(UiDropdownItem, { disabled: true }, () => 'Dupliquer'),
          h(UiDropdownItem, { type: 'checkbox', checked: true }, () => 'Colonne ID'),
          h(UiDropdownItem, { icon: 'trash', danger: true }, () => 'Supprimer'),
        ],
      },
      attachTo: document.body,
    })
  );

const menu = () => document.querySelector<HTMLElement>('[role="menu"]');

describe('UiDropdown', () => {
  it('ouvre le menu au clavier et focalise le premier élément', async () => {
    const wrapper = mountMenu();
    const trigger = wrapper.get('button');
    expect(trigger.attributes('aria-haspopup')).toBe('menu');
    expect(trigger.attributes('aria-expanded')).toBe('false');
    await trigger.trigger('keydown', { key: 'ArrowDown' });
    await flush();
    expect(trigger.attributes('aria-expanded')).toBe('true');
    expect(menu()?.getAttribute('aria-labelledby')).toBe(trigger.attributes('id'));
    expect(document.activeElement?.textContent).toContain('Modifier');
  });

  it('parcourt les éléments actifs avec les flèches, Début et Fin', async () => {
    const wrapper = mountMenu();
    await wrapper.get('button').trigger('keydown', { key: 'ArrowDown' });
    await flush();
    const key = (k: string) => menu()!.dispatchEvent(new KeyboardEvent('keydown', { key: k, bubbles: true }));
    key('ArrowDown');
    expect(document.activeElement?.textContent).toContain('Colonne ID');
    key('End');
    expect(document.activeElement?.textContent).toContain('Supprimer');
    key('ArrowDown');
    expect(document.activeElement?.textContent).toContain('Modifier');
    key('ArrowUp');
    expect(document.activeElement?.textContent).toContain('Supprimer');
  });

  it('se ferme sur Échap et rend le focus au bouton', async () => {
    const wrapper = mountMenu();
    await wrapper.get('button').trigger('keydown', { key: 'ArrowDown' });
    await flush();
    menu()!.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
    await flush();
    expect(menu()).toBeNull();
    expect(document.activeElement).toBe(wrapper.get('button').element);
  });

  it('se ferme au choix d’une action, reste ouvert sur une case', async () => {
    let selected = 0;
    const wrapper = mountMenu(() => selected++);
    await wrapper.get('button').trigger('click');
    await flush();
    const items = () => [...menu()!.querySelectorAll<HTMLElement>('[role^="menuitem"]')];
    expect(items()[2].getAttribute('role')).toBe('menuitemcheckbox');
    expect(items()[2].getAttribute('aria-checked')).toBe('true');
    items()[2].click();
    await flush();
    expect(menu()).not.toBeNull();
    items()[0].click();
    await flush();
    expect(selected).toBe(1);
    expect(menu()).toBeNull();
  });

  it('rend le panneau dans body en position fixe, avec sa flèche', async () => {
    const wrapper = mountMenu();
    await wrapper.get('button').trigger('click');
    await flush();
    expect(menu()?.parentElement).toBe(document.body);
    expect(menu()?.className).toContain('fixed');
    expect(menu()?.className).toContain('ui-arrow');
  });
});

describe('UiTooltip', () => {
  it('apparaît au focus, décrit le bouton et se ferme sur Échap', async () => {
    const wrapper = mount(UiTooltip, {
      props: { text: 'Modifier le tag' },
      slots: { default: () => h('button', { 'aria-label': 'Modifier' }) },
      attachTo: document.body,
    });
    const tip = document.querySelector<HTMLElement>('[role="tooltip"]')!;
    const button = wrapper.get('button');
    expect(button.attributes('aria-describedby')).toBe(tip.id);
    expect(tip.style.display).toBe('none');
    await button.trigger('focusin');
    await new Promise((r) => setTimeout(r, 5));
    expect(tip.style.display).toBe('');
    await button.trigger('keydown', { key: 'Escape' });
    await new Promise((r) => setTimeout(r, 5));
    expect(tip.style.display).toBe('none');
  });
});

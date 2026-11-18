import { afterEach, describe, expect, it, vi } from 'vitest';
import { h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import AdminSidebar from '@/vue/Components/Admin/Layout/AdminSidebar.vue';
import AdminShell from '@/vue/Components/Admin/Layout/AdminShell.vue';
import AdminBreadcrumb from '@/vue/Components/Admin/Layout/AdminBreadcrumb.vue';
import AdminHeader from '@/vue/Components/Admin/Layout/AdminHeader.vue';
import AdminPageHeader from '@/vue/Components/Admin/Layout/AdminPageHeader.vue';
import AdminDebugBar from '@/vue/Components/Admin/Layout/AdminDebugBar.vue';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';
import { withLabels } from '../../Ui/__tests__/support';

afterEach(() => {
  document.body.innerHTML = '';
  document.documentElement.className = '';
  document.documentElement.removeAttribute('data-theme');
});

const items: SidebarItem[] = [
  { id: 'dashboard', label: 'Tableau de bord', href: '/admin', icon: 'M4 5h4' },
  {
    id: 'content',
    label: 'Contenus',
    children: [
      {
        id: 'tag',
        label: 'Tags',
        children: [
          { id: 'tag_index', label: 'Gestion des tags', href: '/admin/tag', active: true },
          { id: 'tag_new', label: 'Nouveau tag', href: '/admin/tag/new' },
        ],
      },
      {
        id: 'comment',
        label: 'Commentaires',
        badge: 2,
        children: [{ id: 'comment_index', label: 'Gestion', href: '/c' }],
      },
    ],
  },
];

describe('AdminSidebar', () => {
  it('affiche les dossiers de premier niveau en groupes et ouvre celui de l’entrée active', () => {
    const wrapper = mount(AdminSidebar, withLabels({ props: { items, siteName: 'Nathéo', version: '2.1.0' } }));
    expect(wrapper.get('h2').text()).toBe('Contenus');
    const [tags, comments] = wrapper.findAll('button[aria-expanded]');
    expect(tags.attributes('aria-expanded')).toBe('true');
    expect(comments.attributes('aria-expanded')).toBe('false');
    expect(comments.text()).toContain('2');
    expect(wrapper.get('[aria-current="page"]').text()).toBe('Gestion des tags');
    expect(wrapper.text()).toContain('sidebar.version(2.1.0)');
  });

  it('ouvre et ferme un dossier', async () => {
    const wrapper = mount(AdminSidebar, withLabels({ props: { items, siteName: 'Nathéo' } }));
    const comments = wrapper.findAll('button[aria-expanded]')[1];
    await comments.trigger('click');
    expect(comments.attributes('aria-expanded')).toBe('true');
    const panel = wrapper.get(`#${comments.attributes('aria-controls')}`);
    expect(panel.attributes('inert')).toBeUndefined();
  });

  it('laisse l’appelant rendre les liens (routeur)', () => {
    const wrapper = mount(
      AdminSidebar,
      withLabels({
        props: { items, siteName: 'Nathéo' },
        slots: { link: ({ item }: { item: SidebarItem }) => h('span', { class: 'router-link' }, item.label) },
      })
    );
    expect(wrapper.findAll('.router-link').map((l) => l.text())).toContain('Nouveau tag');
  });
});

describe('AdminShell', () => {
  it('a un lien d’évitement vers le contenu', () => {
    const wrapper = mount(AdminShell, withLabels({ slots: { default: () => h('p', 'Page') } }));
    expect(wrapper.get('a[href="#main-content"]').text()).toBe('shell.skip_to_content');
    expect(wrapper.get('main').attributes('id')).toBe('main-content');
  });

  it('ferme le panneau mobile sur Échap et rend le focus au bouton menu', async () => {
    window.matchMedia = vi
      .fn()
      .mockReturnValue({ matches: true, addEventListener: vi.fn(), removeEventListener: vi.fn() });
    const wrapper = mount(
      AdminShell,
      withLabels({
        props: { navOpen: false, 'onUpdate:navOpen': (v: boolean) => wrapper.setProps({ navOpen: v }) },
        slots: {
          sidebar: () => h('nav', [h('a', { href: '/admin' }, 'Lien')]),
          header: ({ openNav }: { openNav: () => void }) =>
            h('button', { 'data-nav-toggle': '', onClick: openNav }, 'Menu'),
        },
        attachTo: document.body,
      })
    );
    await wrapper.get('[data-nav-toggle]').trigger('click');
    await nextTick();
    await nextTick();
    expect(wrapper.find('[role="dialog"]').exists()).toBe(true);
    expect(document.activeElement?.textContent).toBe('Lien');
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await nextTick();
    expect(wrapper.props('navOpen')).toBe(false);
    expect(document.activeElement).toBe(wrapper.get('[data-nav-toggle]').element);
  });
});

describe('AdminBreadcrumb et AdminPageHeader', () => {
  it('marque la page courante', () => {
    const wrapper = mount(
      AdminBreadcrumb,
      withLabels({ props: { items: [{ label: 'Tableau de bord', href: '/admin' }, { label: 'Gestion des tags' }] } })
    );
    expect(wrapper.get('nav').attributes('aria-label')).toBe('breadcrumb.nav');
    expect(wrapper.get('[aria-current="page"]').text()).toBe('Gestion des tags');
  });

  it('affiche le titre en h1', () => {
    const wrapper = mount(AdminPageHeader, { props: { title: 'Gestion des tags', icon: 'tag' } });
    expect(wrapper.get('h1').text()).toBe('Gestion des tags');
  });
});

describe('AdminHeader', () => {
  it('applique et mémorise le mode et la couleur', async () => {
    const wrapper = mount(
      AdminHeader,
      withLabels({ props: { section: { group: 'Contenus', label: 'Tags' } }, attachTo: document.body })
    );
    expect(wrapper.text()).toContain('Tags');
    const appearance = wrapper.get('button[aria-label="header.appearance"]');
    await appearance.trigger('click');
    await nextTick();
    await nextTick();
    const radios = [...document.querySelectorAll<HTMLElement>('[role="menuitemradio"]')];
    radios.find((r) => r.textContent?.includes('header.mode_dark'))!.click();
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(localStorage.getItem('darkMode')).toBe('dark');
    await appearance.trigger('click');
    await nextTick();
    await nextTick();
    [...document.querySelectorAll<HTMLElement>('[role="menuitemradio"]')]
      .find((r) => r.textContent?.includes('header.theme_green'))!
      .click();
    expect(document.documentElement.getAttribute('data-theme')).toBe('green');
    expect(localStorage.getItem('theme')).toBe('green');
  });

  it('transmet la recherche globale', async () => {
    const wrapper = mount(AdminHeader, withLabels({ props: { search: 'vue' } }));
    await wrapper.get('form').trigger('submit');
    expect(wrapper.emitted('submitSearch')?.[0]).toEqual(['vue']);
  });
});

describe('AdminDebugBar', () => {
  it('ouvre son panneau et se ferme sur Échap', async () => {
    const wrapper = mount(
      AdminDebugBar,
      withLabels({
        props: {
          env: 'dev',
          branch: 'feature/N-517',
          sections: [{ title: 'Système', entries: [{ label: 'PHP', value: '8.5' }] }],
        },
        attachTo: document.body,
      })
    );
    const button = wrapper.get('button[aria-expanded]');
    await button.trigger('click');
    expect(button.attributes('aria-expanded')).toBe('true');
    expect(wrapper.text()).toContain('PHP');
    await wrapper.get('section').trigger('keydown', { key: 'Escape' });
    expect(button.attributes('aria-expanded')).toBe('false');
  });
});

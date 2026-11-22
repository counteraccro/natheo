import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import { createMemoryHistory, createRouter } from 'vue-router';
import NatheoAtelierLayout from '@/vue/Components/Admin/Themes/NatheoAtelier/NatheoAtelierLayout.vue';
import AtelierCommandPalette from '@/vue/Components/Admin/Themes/NatheoAtelier/AtelierCommandPalette.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { buildUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';

const items: SidebarItem[] = [
  { id: 'dashboard', label: 'Tableau de bord', href: '/admin/fr/dashboard/index' },
  {
    id: 'content',
    label: 'Contenus',
    children: [
      {
        id: 'tag',
        label: 'Tags',
        children: [
          { id: 'tag_index', label: 'Gestion des tags', href: '/admin/fr/tags', icon: 'M1 1h2', active: true },
          { id: 'tag_new', label: 'Nouveau tag', href: '/admin/fr/tags/new', icon: 'M2 2h3' },
        ],
      },
      { id: 'media', label: 'Médiathèque', href: '/admin/fr/media/index' },
    ],
  },
  {
    id: 'system',
    label: 'Système',
    children: [{ id: 'translation', label: 'Traductions', href: '/admin/fr/translation' }],
  },
];
const shortcuts = [{ id: 'tag_new', label: 'Nouveau tag', href: '/admin/fr/tags/new' }];

let wrapper: VueWrapper | null = null;

function mountWith(component: object, props: Record<string, unknown>) {
  const data = bootstrap();
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/', component: { render: () => null } }],
  });
  wrapper = mount(component, {
    props,
    attachTo: document.body,
    slots: { default: '<p class="screen">Écran</p>' },
    global: {
      plugins: [router],
      provide: {
        [ADMIN_CONTEXT as symbol]: createAdminContext(data),
        [UI_LABELS as symbol]: buildUiLabels((key) => key),
      },
    },
  });
  return wrapper;
}

afterEach(() => {
  wrapper?.unmount();
  wrapper = null;
});

describe('NatheoAtelierLayout', () => {
  it('montre les sections, les modules de la section courante et l’écran', () => {
    const layout = mountWith(NatheoAtelierLayout, { items, section: null, shortcuts, bootstrap: bootstrap() });
    const sections = layout.get('nav[aria-label="admin_app.atelier.sections"]');
    expect(sections.findAll('button').map((button) => button.text())).toEqual([
      'Tableau de bord',
      'Contenus',
      'Système',
    ]);
    expect(sections.get('[aria-pressed="true"]').text()).toBe('Contenus');
    const modules = layout.get('nav[aria-label="Contenus"]');
    expect(modules.text()).toContain('Tags');
    expect(modules.get('a[href="/admin/fr/media/index"]').attributes('aria-current')).toBeUndefined();
    expect(modules.get('[data-active]').text()).toContain('Tags');
    expect(layout.find('main .screen').exists()).toBe(true);
  });

  it('montre les sous-pages d’un module avec leur icône et la page courante', async () => {
    const layout = mountWith(NatheoAtelierLayout, { items, section: null, shortcuts, bootstrap: bootstrap() });
    await layout.get('nav[aria-label="Contenus"] [data-active]').trigger('click');
    await nextTick();
    const entries = [...document.querySelectorAll<HTMLElement>('[role="menuitem"]')];
    expect(entries.map((entry) => entry.querySelector('path')?.getAttribute('d'))).toEqual(['M1 1h2', 'M2 2h3']);
    expect(entries[0].getAttribute('aria-current')).toBe('page');
  });

  it('affiche les modules d’une autre section choisie', async () => {
    const layout = mountWith(NatheoAtelierLayout, { items, section: null, shortcuts, bootstrap: bootstrap() });
    const system = layout.findAll('nav[aria-label="admin_app.atelier.sections"] button')[2];
    await system.trigger('click');
    expect(layout.get('nav[aria-label="Système"]').find('a[href="/admin/fr/translation"]').exists()).toBe(true);
  });
});

describe('AtelierCommandPalette', () => {
  const options = () => [...document.querySelectorAll<HTMLElement>('[role="option"]')];

  it('filtre les écrans et les raccourcis sans tenir compte des accents', async () => {
    const palette = mountWith(AtelierCommandPalette, { items, shortcuts, open: true });
    await nextTick();
    await palette.get('input').setValue('mediatheque');
    expect(options().map((option) => option.textContent?.trim())).toEqual(['MédiathèqueContenus']);

    await palette.get('input').setValue('nouveau');
    expect(options().map((option) => option.textContent?.trim())).toEqual(['Nouveau tagContenus', 'Nouveau tag']);
  });

  it('ouvre l’option choisie au clavier', async () => {
    const palette = mountWith(AtelierCommandPalette, { items, shortcuts, open: true });
    await nextTick();
    const input = palette.get('input');
    await input.setValue('tag');
    await input.trigger('keydown', { key: 'ArrowDown' });
    expect(input.attributes('aria-activedescendant')).toBe(options()[1].id);
    await input.trigger('keydown', { key: 'Enter' });
    expect(palette.emitted('openHref')).toEqual([['/admin/fr/tags/new']]);
    expect(palette.emitted('update:open')).toEqual([[false]]);
  });

  it('propose la recherche dans tout le site sans résultat', async () => {
    const palette = mountWith(AtelierCommandPalette, { items, shortcuts, open: true });
    await nextTick();
    await palette.get('input').setValue('zzz');
    expect(options()).toHaveLength(1);
    await palette.get('input').trigger('keydown', { key: 'Enter' });
    expect(palette.emitted('search')).toEqual([['zzz']]);
  });
});

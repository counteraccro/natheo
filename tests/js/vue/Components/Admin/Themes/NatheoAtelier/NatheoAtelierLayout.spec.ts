import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import NatheoAtelierLayout from '@/vue/Components/Admin/Themes/NatheoAtelier/NatheoAtelierLayout.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { buildUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import type { SidebarItem } from '@ui';

const items: SidebarItem[] = [
  {
    id: 'content',
    label: 'Contenus',
    children: [
      { id: 'tag', label: 'Tags', href: '/admin/fr/tags', active: true },
      {
        id: 'comment',
        label: 'Commentaires',
        badge: 3,
        children: [
          { id: 'comment_index', label: 'Gestion', href: '/admin/fr/comment/index' },
          { id: 'comment_moderate', label: 'À modérer', href: '/admin/fr/comment/moderate', badge: 3 },
        ],
      },
    ],
  },
  { id: 'system', label: 'Système', children: [{ id: 'user', label: 'Utilisateurs', href: '/admin/fr/user' }] },
];

function mountLayout() {
  const data = bootstrap();
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/', component: { render: () => null } }],
  });
  return mount(NatheoAtelierLayout, {
    props: { items, section: null, shortcuts: [], bootstrap: data },
    attachTo: document.body,
    global: {
      plugins: [router],
      provide: {
        [ADMIN_CONTEXT as symbol]: createAdminContext(data),
        [UI_LABELS as symbol]: buildUiLabels((key) => key),
      },
    },
  });
}

describe('NatheoAtelierLayout', () => {
  it('montre le compteur d’une entrée sur sa section et sur l’onglet de son module, sans le compter deux fois', () => {
    const wrapper = mountLayout();
    const sections = wrapper.findAll('header nav button');
    expect(sections.map((button) => button.text())).toEqual(['Contenus 3', 'Système']);
    const tab = wrapper.findAll('.atelier-tab').find((candidate) => candidate.text().startsWith('Commentaires'));
    expect(tab?.text()).toContain('3');
    wrapper.unmount();
  });
});

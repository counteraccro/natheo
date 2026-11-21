import { describe, expect, it, vi } from 'vitest';

import { flushPromises, mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import CollectionScreen from '@/vue/Components/Admin/Themes/NatheoStudio/Screens/CollectionScreen.vue';
import FormScreen from '@/vue/Components/Admin/Themes/NatheoStudio/Screens/FormScreen.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { createAdminCollection } from '@/vue/Composables/Admin/useAdminCollection';
import { createCollectionScreen } from '@/vue/Composables/Admin/Screen/useCollectionScreen';
import { createAdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { createFormScreen } from '@/vue/Composables/Admin/Screen/useFormScreen';
import { defineCollectionScreen, defineFormScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';

type Tag = {
  id: number;
  label: string;
  color: string;
  disabled: boolean;
  author: { id: number; login: string } | null;
  translations: { locale: string; label: string }[];
  permissions: { edit: boolean; disable: boolean; delete: boolean };
};

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;
const can: AdminCan = {
  canOperation: () => true,
  can: (action, _operation, item) => item?.permissions?.[action] !== false,
  hasRole: () => false,
};

function mountOptions() {
  const router = createRouter({
    history: createMemoryHistory('/admin/fr/'),
    routes: [
      { path: '/tags', name: 'tag_index', component: { render: () => null } },
      { path: '/tags/new', name: 'tag_new', component: { render: () => null } },
      { path: '/tags/:id', name: 'tag_edit', component: { render: () => null } },
    ],
  });
  const context = { ...createAdminContext(bootstrap()), t };
  return {
    global: { plugins: [router], provide: { [ADMIN_CONTEXT as symbol]: context, [UI_LABELS as symbol]: labels } },
  };
}

const tag = (id: number, values: Partial<Tag> = {}): Tag => ({
  id,
  label: 'Tag ' + id,
  color: '#112233',
  disabled: false,
  author: { id: 1, login: 'R-Admin' },
  translations: [],
  permissions: { edit: true, disable: true, delete: true },
  ...values,
});

describe('CollectionScreen (Natheo-Studio)', () => {
  it('charge la collection et rend titre, pastille, auteur, ligne désactivée grisée et requête SQL', async () => {
    const getCollection = vi.fn().mockResolvedValue({
      members: [tag(1), tag(2, { disabled: true, author: null })],
      total: 2,
      page: 1,
      lastPage: 1,
      sql: 'SELECT 1',
    });
    const api = { getCollection } as unknown as ApiClient;
    const descriptor = defineCollectionScreen<Tag>({
      resource: '/admin/tags',
      title: 'tag.index.title',
      columns: [
        { key: 'label', label: 'tag.index.column_label', type: 'badge', color: 'color' },
        { key: 'author', label: 'tag.index.column_author', type: 'author' },
      ],
      filters: ['status'],
      create: { route: 'tag_new', label: 'tag.index.button_new', operation: 'admin_tag_post' },
      actions: [{ type: 'delete', operation: 'admin_tag_delete' }],
      bulkActions: [{ type: 'delete', operation: 'admin_tag_delete' }],
      rowLabel: (row) => row.label,
      messages: { confirmDisable: 'a', confirmDelete: 'b', disabled: 'c', enabled: 'd', deleted: 'e' },
    });
    const screen = createCollectionScreen(descriptor, {
      api,
      t,
      can,
      confirm: vi.fn(),
      router: { push: vi.fn() },
      collection: createAdminCollection<Tag>(api, '/admin/tags', { withSql: true }),
    });

    const wrapper = mount(CollectionScreen, { props: { screen }, ...mountOptions() });
    await flushPromises();
    expect(getCollection).toHaveBeenCalledTimes(1);
    expect(wrapper.find('h1').text()).toBe('tag.index.title');
    expect(wrapper.text()).toContain('tag.index.button_new');
    const rows = wrapper.findAll('tbody tr');
    expect(rows).toHaveLength(2);
    expect(rows[0].text()).toContain('Tag 1');
    expect(rows[0].text()).toContain('R-Admin');
    expect(rows[1].classes()).toContain('text-fg-subtle');
    expect(rows[1].text()).toContain('—');
    expect(screen.collection.sql.value).toBe('SELECT 1');
  });
});

describe('FormScreen (Natheo-Studio)', () => {
  it('rend un onglet par langue, envoie le formulaire et montre le conflit', async () => {
    const getResource = vi.fn().mockResolvedValue({
      data: tag(4, { translations: [{ locale: 'fr', label: 'Été' }] }),
      etag: '"v1"',
    });
    const resource = createAdminResource<Tag>({ getResource } as unknown as ApiClient, t, '/admin/tags', 4, {
      leaveKey: 'tag_form_test',
    });
    const descriptor = defineFormScreen<Tag>({
      resource: '/admin/tags',
      title: { create: 'tag.form.title_new', edit: 'tag.form.title_edit' },
      back: 'tag_index',
      editRoute: 'tag_edit',
      leaveKey: 'tag_form_test',
      initial: () => ({}),
      sections: [
        {
          title: 'tag.form.section_label',
          fields: [{ type: 'translations', name: 'translations', field: 'label', label: 'tag.form.label' }],
        },
        {
          title: 'tag.form.section_style',
          description: 'tag.form.section_style_description',
          kind: 'appearance',
          fields: [{ type: 'color', name: 'color', label: 'tag.form.color' }],
        },
      ],
      meta: ['author'],
      update: { operation: 'admin_tag_patch' },
      delete: { operation: 'admin_tag_delete' },
      messages: { created: 'a', saved: 'b', confirmDelete: 'c', deleted: 'd' },
      rowLabel: (item) => item.label,
    });
    const screen = createFormScreen(descriptor, {
      t,
      can,
      confirm: vi.fn(),
      router: { push: vi.fn(), replace: vi.fn() },
      resource,
      locales: ['fr', 'en', 'es'],
      currentLocale: 'fr',
    });
    const save = vi.spyOn(screen, 'save').mockResolvedValue();
    await resource.load();

    const wrapper = mount(FormScreen, { props: { screen }, ...mountOptions() });
    await flushPromises();
    expect(wrapper.find('h1').text()).toBe('tag.form.title_edit {"id":"4"}');
    expect(wrapper.findAll('[role="tab"]')).toHaveLength(3);
    const [labelHead, styleHead] = wrapper.findAll('form section > header');
    expect(labelHead.find('[aria-hidden="true"] svg').exists()).toBe(false);
    expect(styleHead.find('[aria-hidden="true"] svg').exists()).toBe(true);
    expect(styleHead.text()).toContain('tag.form.section_style_description');
    expect((wrapper.find('input[type="text"]').element as HTMLInputElement).value).toBe('Été');
    expect(wrapper.text()).toContain('R-Admin');
    expect(wrapper.text()).toContain('admin_app.screen.action_delete');

    await wrapper.find('form').trigger('submit');
    expect(save).toHaveBeenCalled();
    // Le panneau d'actions de la colonne de droite est hors du formulaire, relié par son id
    const formId = wrapper.get('form').attributes('id');
    expect(formId).toBeTruthy();
    expect(wrapper.get('aside button[type="submit"]').attributes('form')).toBe(formId);

    resource.conflict.value = true;
    await flushPromises();
    expect(wrapper.text()).toContain('admin_app.resource.conflict_title');
    leaveGuard.untrack('tag_form_test');
  });
});

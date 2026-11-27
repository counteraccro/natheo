import { describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';

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
import type { CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';

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
      read: { operation: 'admin_tag_get_collection' },
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
      router: { push: vi.fn(), resolve: () => ({ href: '' }) },
      collection: createAdminCollection<Tag>(api, '/admin/tags', { withSql: true }),
      locale: 'fr',
    });

    const wrapper = mount(CollectionScreen, {
      // mount() ne déduit pas le générique du composant, qui reste à ScreenItem
      props: { screen: screen as unknown as CollectionScreenState },
      ...mountOptions(),
    });
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

describe('CollectionScreen (Natheo-Studio), ajouts du module', () => {
  it('affiche le composant du module, ses filtres et le dialogue de son action groupée', async () => {
    const getCollection = vi.fn().mockResolvedValue({ members: [tag(1)], total: 1, page: 1, lastPage: 1 });
    const patch = vi.fn().mockResolvedValue({});
    const api = { getCollection, patch } as unknown as ApiClient;
    const Header = defineComponent({
      props: { screen: { type: Object, required: true } },
      setup: (props) => () => h('p', { class: 'module-header' }, `${props.screen.descriptor.title}`),
    });
    const Dialog = defineComponent({
      props: { count: { type: Number, required: true }, kept: { type: Number, required: true } },
      emits: ['submit', 'cancel'],
      setup:
        (props, { emit }) =>
        () =>
          h('button', { class: 'module-dialog', onClick: () => emit('submit', { reason: 'x' }) }, `${props.count}`),
    });
    const descriptor = defineCollectionScreen<Tag>({
      resource: '/admin/tags',
      read: { operation: 'admin_tag_get_collection' },
      title: 'tag.index.title',
      columns: [{ key: 'label', label: 'tag.index.column_label' }],
      header: Header,
      filters: [
        {
          type: 'select',
          param: 'moderation',
          label: 'comment.filter.moderation',
          allLabel: 'comment.filter.moderation_all',
          options: [{ value: 'moderate', label: 'comment.status.moderate' }],
        },
      ],
      bulkActions: [
        { type: 'patch', id: 'moderate', label: 'm', icon: 'ban', operation: 'admin_tag_patch', dialog: Dialog },
      ],
      rowLabel: (row) => row.label,
      messages: {},
    });
    const screen = createCollectionScreen(descriptor, {
      api,
      t,
      can,
      confirm: vi.fn(),
      router: { push: vi.fn(), resolve: () => ({ href: '' }) },
      collection: createAdminCollection<Tag>(api, '/admin/tags'),
      locale: 'fr',
    });
    const wrapper = mount(CollectionScreen, {
      props: { screen: screen as unknown as CollectionScreenState },
      ...mountOptions(),
    });
    await flushPromises();
    expect(wrapper.get('.module-header').text()).toBe('tag.index.title');
    const select = wrapper.get('select[aria-label="comment.filter.moderation"]');
    expect(select.findAll('option').map((option) => option.text())).toEqual([
      'comment.filter.moderation_all',
      'comment.status.moderate',
    ]);
    await select.setValue('moderate');
    await flushPromises();
    expect(getCollection).toHaveBeenLastCalledWith('/admin/tags', expect.objectContaining({ moderation: 'moderate' }));

    const running = screen.runBulk('moderate', [1]);
    await flushPromises();
    await wrapper.get('.module-dialog').trigger('click');
    await running;
    expect(patch).toHaveBeenCalledWith('/admin/tags/1', { reason: 'x' });
    await flushPromises();
    expect(wrapper.find('.module-dialog').exists()).toBe(false);
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
      read: { operation: 'admin_tag_get' },
      create: { operation: 'admin_tag_post' },
      title: { create: 'tag.form.title_new', edit: 'tag.form.title_edit', view: 'tag.form.title_view' },
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

    const wrapper = mount(FormScreen, {
      // mount() ne déduit pas le générique du composant, qui reste à ScreenItem
      props: { screen: screen as unknown as FormScreenState },
      ...mountOptions(),
    });
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

  it('grise les champs et titre en consultation quand l’élément n’est pas modifiable', async () => {
    const getResource = vi.fn().mockResolvedValue({
      data: tag(4, {
        translations: [{ locale: 'fr', label: 'Été' }],
        permissions: { edit: false, disable: false, delete: false },
      }),
      etag: '"v1"',
    });
    const resource = createAdminResource<Tag>({ getResource } as unknown as ApiClient, t, '/admin/tags', 4, {
      leaveKey: 'tag_view_test',
    });
    const descriptor = defineFormScreen<Tag>({
      resource: '/admin/tags',
      read: { operation: 'admin_tag_get' },
      title: { create: 'tag.form.title_new', edit: 'tag.form.title_edit', view: 'tag.form.title_view' },
      back: 'tag_index',
      editRoute: 'tag_edit',
      leaveKey: 'tag_view_test',
      initial: () => ({}),
      sections: [
        {
          title: 'tag.form.section_label',
          fields: [
            { type: 'translations', name: 'translations', field: 'label', label: 'tag.form.label' },
            { type: 'color', name: 'color', label: 'tag.form.color', suggestions: 3 },
            { type: 'switch', name: 'disabled', label: 'tag.form.disabled' },
          ],
        },
      ],
      update: { operation: 'admin_tag_patch' },
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
    await resource.load();

    const wrapper = mount(FormScreen, {
      props: { screen: screen as unknown as FormScreenState },
      ...mountOptions(),
    });
    await flushPromises();
    expect(wrapper.find('h1').text()).toBe('tag.form.title_view {"id":"4"}');
    const controls = wrapper.findAll('form input, form [role="switch"]');
    expect(controls.length).toBeGreaterThan(2);
    expect(controls.every((control) => control.attributes('disabled') !== undefined)).toBe(true);
    expect(wrapper.findAll('[role="tab"]').every((tab) => tab.attributes('disabled') === undefined)).toBe(true);
    expect(wrapper.find('[aria-pressed]').exists()).toBe(false);
    leaveGuard.untrack('tag_view_test');
  });

  it('masque un champ selon visibleWhen et affiche les informations du module avec leur lien', async () => {
    const getResource = vi.fn().mockResolvedValue({ data: tag(4, { disabled: false }), etag: '"v1"' });
    const resource = createAdminResource<Tag>({ getResource } as unknown as ApiClient, t, '/admin/tags', 4, {
      leaveKey: 'tag_visible_test',
    });
    const descriptor = defineFormScreen<Tag>({
      resource: '/admin/tags',
      read: { operation: 'admin_tag_get' },
      title: { create: 'a', edit: 'b', view: 'c' },
      back: 'tag_index',
      leaveKey: 'tag_visible_test',
      initial: () => ({}),
      sections: [
        {
          title: 'tag.form.section_style',
          fields: [
            { type: 'switch', name: 'disabled', label: 'tag.form.disabled' },
            { type: 'text', name: 'label', label: 'tag.form.reason', visibleWhen: (data) => data.disabled === true },
          ],
        },
      ],
      meta: [
        { name: 'label', label: 'tag.meta.label', value: (item) => item.label, href: () => '/fr/page/tag' },
        { name: 'empty', label: 'tag.meta.empty', value: () => null },
      ],
      update: { operation: 'admin_tag_patch' },
      messages: { saved: 'b' },
      rowLabel: (item) => item.label,
    });
    const screen = createFormScreen(descriptor, {
      t,
      can,
      confirm: vi.fn(),
      router: { push: vi.fn(), replace: vi.fn() },
      resource,
      locales: ['fr'],
      currentLocale: 'fr',
    });
    await resource.load();
    const wrapper = mount(FormScreen, { props: { screen: screen as unknown as FormScreenState }, ...mountOptions() });
    await flushPromises();
    expect(wrapper.text()).not.toContain('tag.form.reason');
    await wrapper.get('button[role="switch"]').trigger('click');
    expect(wrapper.text()).toContain('tag.form.reason');

    const link = wrapper.get('aside dd a');
    expect(link.text()).toBe('Tag 4');
    expect(link.attributes('href')).toBe('/fr/page/tag');
    expect(wrapper.get('aside').text()).toContain('—');
    leaveGuard.untrack('tag_visible_test');
  });
});

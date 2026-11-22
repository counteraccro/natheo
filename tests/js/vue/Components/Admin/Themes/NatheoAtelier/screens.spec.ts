import { describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import AtelierList from '@/vue/Components/Admin/Themes/NatheoAtelier/AtelierList.vue';
import { ATELIER_PANE_TOP } from '@/vue/Components/Admin/Themes/NatheoAtelier/atelier';
import CollectionScreen from '@/vue/Components/Admin/Themes/NatheoAtelier/Screens/CollectionScreen.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { defineCollectionScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { notifyResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';
import type { CollectionScreenDescriptor, CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';

type Tag = {
  id: number;
  label: string;
  color: string;
  disabled: boolean;
  pageCount: number;
  permissions: { edit: boolean; disable: boolean; delete: boolean };
};

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;
const can: AdminCan = { canOperation: () => true, can: () => true, hasRole: () => false };

const descriptor = defineCollectionScreen<Tag>({
  resource: '/admin/tags',
  title: 'tag.index.title',
  columns: [
    { key: 'label', label: 'tag.index.column_label', type: 'badge', color: 'color' },
    { key: 'pageCount', label: 'tag.index.column_pages', type: 'number' },
  ],
  filters: ['status', 'author'],
  create: { route: 'tag_new', label: 'tag.index.button_new', operation: 'admin_tag_post' },
  actions: [
    { type: 'edit', route: 'tag_edit', operation: 'admin_tag_patch' },
    { type: 'delete', operation: 'admin_tag_delete' },
  ],
  bulkActions: [{ type: 'delete', operation: 'admin_tag_delete' }],
  rowLabel: (row) => row.label,
  messages: { confirmDisable: 'a', confirmDelete: 'b', disabled: 'c', enabled: 'd', deleted: 'e' },
});

const tag = (id: number, values: Partial<Tag> = {}): Tag => ({
  id,
  label: 'Tag ' + id,
  color: '#112233',
  disabled: false,
  pageCount: id,
  permissions: { edit: true, disable: true, delete: true },
  ...values,
});

async function mountList(path = '/tags/2', sql: string | null = null) {
  const getCollection = vi.fn().mockResolvedValue({
    members: [tag(1), tag(2, { disabled: true })],
    total: 2,
    page: 1,
    lastPage: 1,
    sql,
  });
  const router = createRouter({
    history: createMemoryHistory('/admin/fr/'),
    routes: [
      { path: '/tags', name: 'tag_index', component: { render: () => null } },
      { path: '/tags/new', name: 'tag_new', component: { render: () => null } },
      { path: '/tags/:id', name: 'tag_edit', component: { render: () => null } },
    ],
  });
  await router.push(path);
  const context = { ...createAdminContext(bootstrap()), t, can, api: { getCollection } as unknown as ApiClient };
  const wrapper = mount(AtelierList, {
    attachTo: document.body,
    props: { descriptor: descriptor as unknown as CollectionScreenDescriptor },
    global: { plugins: [router], provide: { [ADMIN_CONTEXT as symbol]: context, [UI_LABELS as symbol]: labels } },
  });
  await flushPromises();
  return { wrapper, getCollection };
}

describe('AtelierList', () => {
  it('rend les lignes avec leur lien, leurs valeurs et la ligne ouverte', async () => {
    const { wrapper } = await mountList();
    const rows = wrapper.findAll('li.atelier-row');
    expect(rows).toHaveLength(2);
    expect(rows[0].get('a').attributes('href')).toBe('/admin/fr/tags/1');
    expect(rows[0].text()).toContain('tag.index.column_pages');
    expect(rows[1].attributes('aria-current')).toBe('true');
    expect(rows[1].text()).toContain('admin_app.screen.status_disabled');
    expect(wrapper.text()).toContain('tag.index.button_new');
  });

  it('filtre par pastille et se recharge quand un formulaire écrit la ressource', async () => {
    const { wrapper, getCollection } = await mountList();
    const chips = wrapper.findAll('button.atelier-chip');
    await chips[2].trigger('click');
    await flushPromises();
    expect(getCollection).toHaveBeenLastCalledWith('/admin/tags', expect.objectContaining({ status: 'disabled' }));
    expect(chips[2].attributes('aria-pressed')).toBe('true');

    const calls = getCollection.mock.calls.length;
    notifyResourceChange('/admin/tags');
    await flushPromises();
    expect(getCollection.mock.calls.length).toBe(calls + 1);
    wrapper.unmount();
  });

  it('affiche la requête SQL en tête du volet', async () => {
    const top = document.createElement('div');
    top.id = ATELIER_PANE_TOP;
    document.body.append(top);
    const { wrapper } = await mountList('/tags/2', 'SELECT 1');
    await wrapper.get('button[aria-label="data_table.options"]').trigger('click');
    await flushPromises();
    [...document.querySelectorAll<HTMLElement>('[role="menuitemcheckbox"]')]
      .find((item) => item.textContent?.includes('data_table.sql_show'))!
      .click();
    await flushPromises();
    expect(top.textContent).toContain('SELECT 1');
    expect(wrapper.element.textContent).not.toContain('SELECT 1');
    wrapper.unmount();
    top.remove();
  });

  it('montre les actions groupées pour une sélection', async () => {
    const { wrapper } = await mountList();
    await wrapper.findAll('li.atelier-row input[type="checkbox"]')[0].setValue(true);
    expect(wrapper.find('[role="region"]').exists()).toBe(true);
  });
});

describe('CollectionScreen (Natheo-Atelier)', () => {
  it('présente l’écran et invite à créer sans charger la collection', async () => {
    const router = createRouter({
      history: createMemoryHistory('/admin/fr/'),
      routes: [{ path: '/tags/new', name: 'tag_new', component: { render: () => null } }],
    });
    const load = vi.fn();
    const screen = { descriptor, canCreate: true, collection: { load } } as unknown as CollectionScreenState;
    const wrapper = mount(CollectionScreen, {
      props: { screen },
      global: {
        plugins: [router],
        provide: {
          [ADMIN_CONTEXT as symbol]: { ...createAdminContext(bootstrap()), t },
          [UI_LABELS as symbol]: labels,
        },
      },
    });
    expect(wrapper.get('h1').text()).toBe('tag.index.title');
    expect(wrapper.get('a[href="/admin/fr/tags/new"]').text()).toContain('tag.index.button_new');
    expect(load).not.toHaveBeenCalled();
  });
});

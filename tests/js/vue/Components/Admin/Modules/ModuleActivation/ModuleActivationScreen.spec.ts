import { describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import ModuleActivationScreen from '@admin/Modules/ModuleActivation/ModuleActivationScreen.vue';
import moduleActivation from '@admin/Modules/ModuleActivation/module';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { buildUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import type { AdminModuleActivation } from '@/ts/Admin/Api/AdminModuleActivation.type';

const modules: AdminModuleActivation[] = [
  {
    '@id': '/api/v2/admin/module_activations/dashboard',
    '@type': 'AdminModuleActivation',
    id: 'dashboard',
    label: 'Tableau de bord',
    description: '',
    locked: true,
    disabled: false,
    operations: [],
  },
  {
    '@id': '/api/v2/admin/module_activations/tag',
    '@type': 'AdminModuleActivation',
    id: 'tag',
    label: 'Tags',
    description: 'Tags des pages',
    locked: false,
    disabled: false,
    operations: [
      {
        '@id': '/api/v2/admin/operation_activations/admin_tag_delete',
        '@type': 'AdminOperationActivation',
        id: 'admin_tag_delete',
        module: 'tag',
        method: 'DELETE',
        path: '/admin/tags/{id}',
        kind: 'delete',
        locked: false,
        disabled: true,
      },
    ],
  },
];

async function mountScreen() {
  const context = createAdminContext(bootstrap());
  const api = { getCollection: vi.fn(() => Promise.resolve({ members: modules, total: 2, page: 1, lastPage: 1 })) };
  const wrapper = mount(ModuleActivationScreen, {
    global: {
      provide: {
        [ADMIN_CONTEXT as symbol]: { ...context, api, t: (key: string) => key },
        [UI_LABELS as symbol]: buildUiLabels((key) => key),
      },
    },
  });
  await flushPromises();
  return wrapper;
}

describe('ModuleActivationScreen', () => {
  it('déclare sa route et son domaine', () => {
    expect(moduleActivation.id).toBe('module_activation');
    expect(moduleActivation.domains).toEqual(['module_activation']);
    expect(moduleActivation.routes.map((route) => [route.path, route.name])).toEqual([
      ['/modules', 'module_activation_index'],
    ]);
  });

  it('affiche un interrupteur par module, désactivé et marqué d’un cadenas pour un module verrouillé', async () => {
    const wrapper = await mountScreen();
    const switches = wrapper.findAll('[role="switch"]');
    expect(switches).toHaveLength(2);
    expect(switches[0].attributes('disabled')).toBeDefined();
    expect(switches[1].attributes('disabled')).toBeUndefined();
    expect(wrapper.find('[aria-label="module_activation.index.module_locked"]').exists()).toBe(true);
  });

  it('déplie les opérations d’un module', async () => {
    const wrapper = await mountScreen();
    const toggle = wrapper.find('button[aria-controls="module-operations-tag"]');
    expect(toggle.attributes('aria-expanded')).toBe('false');
    await toggle.trigger('click');

    expect(toggle.attributes('aria-expanded')).toBe('true');
    const operations = wrapper.find('#module-operations-tag');
    expect(operations.text()).toContain('module_activation.kind.delete');
    expect(operations.text()).toContain('DELETE /admin/tags/{id}');
    expect(operations.find('[role="switch"]').attributes('aria-checked')).toBe('false');
  });
});

import { afterEach, describe, expect, it, vi } from 'vitest';
import { useModuleActivation } from '@/vue/Composables/Admin/ModuleActivation/useModuleActivation';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import * as toast from '@/vue/Composables/Global/toast';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import type { AdminModuleActivation, AdminOperationActivation } from '@/ts/Admin/Api/AdminModuleActivation.type';

function operationOf(id: string, values: Partial<AdminOperationActivation> = {}): AdminOperationActivation {
  return {
    '@id': '/api/v2/admin/operation_activations/' + id,
    '@type': 'AdminOperationActivation',
    id,
    module: 'tag',
    method: 'DELETE',
    path: '/admin/tags/{id}',
    kind: 'delete',
    locked: false,
    disabled: false,
    ...values,
  };
}

function moduleOf(values: Partial<AdminModuleActivation> = {}): AdminModuleActivation {
  return {
    '@id': '/api/v2/admin/module_activations/tag',
    '@type': 'AdminModuleActivation',
    id: 'tag',
    label: 'Tags',
    description: '',
    locked: false,
    disabled: false,
    operations: [operationOf('admin_tag_delete')],
    ...values,
  };
}

/** Écran branché sur un client simulé, réponses des PATCH données par l'appelant */
function screenOf(patch: (path: string, body: { disabled: boolean }) => unknown) {
  const api = {
    getCollection: vi.fn(() => Promise.resolve({ members: [moduleOf()], total: 1, page: 1, lastPage: 1 })),
    patch: vi.fn((path: string, body: { disabled: boolean }) => Promise.resolve(patch(path, body))),
    get: vi.fn(() => Promise.resolve(moduleOf())),
  };
  const menu = { reload: vi.fn(() => Promise.resolve()) };
  const t = (key: string, params?: Record<string, unknown>) => (params ? `${key} ${JSON.stringify(params)}` : key);
  const screen = useModuleActivation({
    api: api as unknown as ApiClient,
    menu: menu as unknown as AdminMenu,
    t,
  });
  return { screen, api, menu };
}

afterEach(() => {
  vi.restoreAllMocks();
  useConfirm().answer(false);
});

describe('useModuleActivation', () => {
  it('charge tous les modules', async () => {
    const { screen, api } = screenOf(() => null);
    await screen.load();
    expect(api.getCollection).toHaveBeenCalledWith('/admin/module_activations');
    expect(screen.modules.value.map((module) => module.id)).toEqual(['tag']);
    expect(screen.loading.value).toBe(false);
  });

  it('désactive un module après confirmation, puis recharge le menu', async () => {
    const notify = vi.spyOn(toast, 'notify');
    const { screen, api, menu } = screenOf(() => moduleOf({ disabled: true }));
    await screen.load();

    const pending = screen.setModuleDisabled(screen.modules.value[0], true);
    expect(useConfirm().current.value?.tone).toBe('danger');
    useConfirm().answer(true);
    await pending;

    expect(api.patch).toHaveBeenCalledWith('/admin/module_activations/tag', { disabled: true });
    expect(screen.modules.value[0].disabled).toBe(true);
    expect(menu.reload).toHaveBeenCalledOnce();
    expect(notify).toHaveBeenCalledWith('success', expect.stringContaining('module_activation.notify.module_disabled'));
    expect(screen.saving.value.size).toBe(0);
  });

  it('ne fait rien quand la désactivation est refusée', async () => {
    const { screen, api } = screenOf(() => null);
    await screen.load();
    const pending = screen.setModuleDisabled(screen.modules.value[0], true);
    useConfirm().answer(false);
    await pending;
    expect(api.patch).not.toHaveBeenCalled();
  });

  it('réactive sans confirmation', async () => {
    const { screen, api } = screenOf(() => moduleOf());
    await screen.load();
    await screen.setModuleDisabled(moduleOf({ disabled: true }), false);
    expect(useConfirm().current.value).toBeNull();
    expect(api.patch).toHaveBeenCalledWith('/admin/module_activations/tag', { disabled: false });
  });

  it('désactive une opération et met à jour son module', async () => {
    const { screen, api } = screenOf(() => operationOf('admin_tag_delete', { disabled: true }));
    api.get.mockResolvedValueOnce(moduleOf({ operations: [operationOf('admin_tag_delete', { disabled: true })] }));
    await screen.load();
    const module = screen.modules.value[0];

    const pending = screen.setOperationDisabled(module, module.operations[0], true);
    useConfirm().answer(true);
    await pending;

    expect(api.patch).toHaveBeenCalledWith('/admin/operation_activations/admin_tag_delete', { disabled: true });
    expect(screen.modules.value[0].operations[0].disabled).toBe(true);
    expect(screen.kindLabel(module.operations[0])).toBe('module_activation.kind.delete');
  });

  it('prévient que la dernière opération active désactive le module', async () => {
    const { screen } = screenOf(() => operationOf('admin_tag_delete', { disabled: true }));
    await screen.load();
    const module = moduleOf({
      operations: [operationOf('admin_tag_delete'), operationOf('admin_tag_get', { disabled: true })],
    });

    const pending = screen.setOperationDisabled(module, module.operations[0], true);
    expect(useConfirm().current.value?.message).toBe(
      'module_activation.dialog.disable_last_operation_text {"label":"Tags"}'
    );
    useConfirm().answer(false);
    await pending;
  });

  it('relit le module quand une opération réactive son module désactivé', async () => {
    const { screen, api } = screenOf(() => operationOf('admin_tag_delete'));
    await screen.load();
    screen.modules.value = [
      moduleOf({ disabled: true, operations: [operationOf('admin_tag_delete', { disabled: true })] }),
    ];
    const module = screen.modules.value[0];

    await screen.setOperationDisabled(module, module.operations[0], false);

    expect(api.get).toHaveBeenCalledWith('/admin/module_activations/tag');
    expect(screen.modules.value[0].disabled).toBe(false);
    expect(screen.modules.value[0].operations[0].disabled).toBe(false);
  });

  it('affiche l’erreur de l’API, sans changer l’état', async () => {
    const notify = vi.spyOn(toast, 'notify');
    const { screen, menu } = screenOf(() => {
      throw new ApiError(409, 'Conflict', 'Ce module est verrouillé');
    });
    await screen.load();
    await screen.setModuleDisabled(moduleOf(), false);
    expect(notify).toHaveBeenCalledWith('danger', 'Conflict', 'Ce module est verrouillé');
    expect(menu.reload).not.toHaveBeenCalled();
    expect(screen.modules.value[0].disabled).toBe(false);
  });
});

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * État et actions de l'écran d'activation des modules du back-office et de leurs opérations
 */
import { ref, type Ref } from 'vue';
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';
import type { AdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type {
  AdminModuleActivation,
  AdminOperationActivation,
  OperationKind,
} from '@/ts/Admin/Api/AdminModuleActivation.type';

/** Libellé de chaque nature d'opération */
const KIND_LABELS: Record<OperationKind, string> = {
  get_collection: t('module_activation.kind.get_collection'),
  get: t('module_activation.kind.get'),
  post: t('module_activation.kind.post'),
  put: t('module_activation.kind.put'),
  patch: t('module_activation.kind.patch'),
  delete: t('module_activation.kind.delete'),
};

export interface ModuleActivationScreen {
  modules: Ref<AdminModuleActivation[]>;
  loading: Ref<boolean>;
  /** Identifiants (module ou opération) dont l'écriture est en cours */
  saving: Ref<Set<string>>;
  load(): Promise<void>;
  setModuleDisabled(module: AdminModuleActivation, disabled: boolean): Promise<void>;
  setOperationDisabled(
    module: AdminModuleActivation,
    operation: AdminOperationActivation,
    disabled: boolean
  ): Promise<void>;
  kindLabel(operation: AdminOperationActivation): string;
}

/**
 * Écran d'activation, branché sur le client et le menu du contexte
 * @param context contexte de l'application
 */
export function useModuleActivation(context: Pick<AdminContext, 'api' | 'menu' | 't'>): ModuleActivationScreen {
  const { api, menu, t } = context;
  const { confirm } = useConfirm();
  const modules = ref<AdminModuleActivation[]>([]);
  const loading = ref(true);
  const saving = ref(new Set<string>());

  const fail = (caught: unknown): void => {
    const error = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
    notify('danger', error.title, error.detail ?? undefined);
  };

  /** Écriture d'un élément, marqué en cours pendant l'appel */
  const run = async (key: string, write: () => Promise<void>): Promise<void> => {
    saving.value = new Set([...saving.value, key]);
    try {
      await write();
    } catch (caught) {
      fail(caught);
    } finally {
      const next = new Set(saving.value);
      next.delete(key);
      saving.value = next;
    }
  };

  const replaceModule = (updated: AdminModuleActivation): void => {
    modules.value = modules.value.map((module) => (module.id === updated.id ? updated : module));
  };

  return {
    modules,
    loading,
    saving,
    async load(): Promise<void> {
      loading.value = true;
      try {
        modules.value = (await api.getCollection<AdminModuleActivation>('/admin/module_activations')).members;
      } catch (caught) {
        fail(caught);
      } finally {
        loading.value = false;
      }
    },
    async setModuleDisabled(module: AdminModuleActivation, disabled: boolean): Promise<void> {
      if (
        disabled &&
        !(await confirm({
          title: t('module_activation.dialog.disable_module', { label: module.label }),
          message: t('module_activation.dialog.disable_module_text'),
          tone: 'danger',
          icon: 'ban',
          confirmLabel: t('module_activation.dialog.disable'),
          confirmIcon: 'ban',
        }))
      ) {
        return;
      }
      await run(module.id, async () => {
        replaceModule(await api.patch<AdminModuleActivation>(`/admin/module_activations/${module.id}`, { disabled }));
        await menu.reload();
        notify(
          'success',
          disabled
            ? t('module_activation.notify.module_disabled', { label: module.label })
            : t('module_activation.notify.module_enabled', { label: module.label })
        );
      });
    },
    async setOperationDisabled(
      module: AdminModuleActivation,
      operation: AdminOperationActivation,
      disabled: boolean
    ): Promise<void> {
      const label = `${operation.method} ${operation.path}`;
      const last =
        !module.locked && module.operations.every((candidate) => candidate.id === operation.id || candidate.disabled);
      if (
        disabled &&
        !(await confirm({
          title: t('module_activation.dialog.disable_operation', { label }),
          message: last
            ? t('module_activation.dialog.disable_last_operation_text', { label: module.label })
            : t('module_activation.dialog.disable_operation_text'),
          tone: 'danger',
          icon: 'ban',
          confirmLabel: t('module_activation.dialog.disable'),
          confirmIcon: 'ban',
        }))
      ) {
        return;
      }
      await run(operation.id, async () => {
        await api.patch<AdminOperationActivation>(`/admin/operation_activations/${operation.id}`, { disabled });
        // Une opération peut activer ou désactiver son module, et donc l'état affiché des autres
        replaceModule(await api.get<AdminModuleActivation>(`/admin/module_activations/${module.id}`));
        await menu.reload();
        notify(
          'success',
          disabled
            ? t('module_activation.notify.operation_disabled', { label })
            : t('module_activation.notify.operation_enabled', { label })
        );
      });
    },
    kindLabel(operation: AdminOperationActivation): string {
      return t(KIND_LABELS[operation.kind] ?? KIND_LABELS.get);
    },
  };
}
